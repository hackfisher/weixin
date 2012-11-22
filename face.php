<?php
// load php_client_demo, this can be downloaded from our website(http://api.face-plus-plus.com/docs/download/sdk)
require_once(__DIR__ . "/FacePPClient.php");

// your api_key and api_secret
$api_key = "e336859e1099669c6662a3ca76c590b8";
$api_secret = "HcMo_lQFQ4RZ1qrijxjjnzoHeNjxMc18";
// initialize client object
$api = new FacePPClient($api_key, $api_secret);
// the list of person_name to train and recognize for
$person_names = array("denny", "beauty");
// store the face_ids obtained by detection/detect API
$face_ids = array();
// register new people, detect faces
foreach ($person_names as $person_name)
    detect($api, $person_name, $face_ids);
    
// the name of group for testing
$group = "sample_group";
// generate a new group, add people into group
create_group($api, $group, $person_names);

// generate training model for group
train($api, $group);
    
// finally, search people in the group
recognize($api, $person_names[0], $group);

/* 
 *	create new person, detect faces from person's image_url
 */
function detect(&$api, $person_name, &$face_ids) 
{
	// obtain photo_url to train
    $url = getTrainingUrl($person_name);
    
    // detect faces in this photo
    $result = $api->face_detect($url);
    // skip errors
    if (empty($result->face))
        return false;
    // skip photo with multiple faces (we are not sure which face to train)
   	if (count($result->face) > 1)
   		return false;
   	
   	// obtain the face_id
   	$face_id = $result->face[0]->face_id;
   	$face_ids[] = $face_id;
   	// create a new person for this face
   	$api->person_create($person_name);
   	// add face into new person
   	$api->person_add_face($face_id, $person_name);
}

/*
 *	train recognization model for group
 */
function train(&$api, $group_name)
{
   	// train model
   	$session = $api->recognition_train($group_name);
    if (empty($session->session_id))
    {
        // something went wrong, skip
        return false;
    }
    $session_id = $session->session_id;
    // wait until training process done
    while ($session=$api->info_get_session($session_id)) 
    {
        sleep(1);

        if (!empty($session->status)) {
            if ($session->status != "INQUEUE")
                break;
        }
    }
	// done
    return true;
}

/*
 *	recognize a person in group
 */
function recognize(&$api, $person_name, $group_name)
{
	// obtain photo_url to recognize
	$url = getPhotoUrl($person_name);
	
	// recoginzation
	$result = $api->recognition_recognize($url, $group_name);
	
	// skip errors
	if (empty($result->face))
		return false;
	// skip photo with multiple faces
	if (count($result->face) > 1)
		return false;
	$face = $result->face[0];
	// skip if no person returned
	if (count($face->candidate) < 1)
		return false;
		
	// print result
	foreach ($face->candidate as $candidate) 
		echo "$candidate->person_name was found in $group_name with ".
        "confidence $candidate->confidence\n";
}

/*
 *	generate a new group with group_name, add all people into group
 */
function create_group(&$api, $group_name, $person_names) 
{
	$api->group_create($group_name);
   	// add new person into the group
	foreach ($person_names as $person_name)
	   	$api->group_add_person($person_name, $group_name);
}

/*
 *	return the train data(image_url) of $person_name
 */
function getTrainingUrl($person_name)
{
    // TODO: here is just the fake url
	// "http://face-plus-plus.com/static/img/demo/".$person_name.".jpg";
    return "http://nf.hackfisher.info/wximages/".$person_name.".jpg";
}

/*
 *	return the photo_url of $person_name to recognize for
 */
function getPhotoUrl($person_name)
{
    // TODO: here is just the fake url
    return "http://nf.hackfisher.info/wximages/".$person_name.".jpg";
}

?>
