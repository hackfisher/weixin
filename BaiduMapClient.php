<?php
define ("DEBUG_MODE", false);

class BaiduMapClient
{	
	private $api_server_url;
	private $auth_params;

	public function __construct($api_key)
	{
		$this->api_server_url = "http://api.map.baidu.com/";
    	$this->auth_params = array();
   		$this->auth_params['key'] = $api_key;
	}
	
	//////////////////////////////////////////////////////////
	// public mathods
	//////////////////////////////////////////////////////////
	
	public function place_search($query, $location, $radius) 
	{
		return $this->call("place/search", array("query" => $query,
														 "location" => $location, "radius" => $radius, "output" => "json"));
	}
	
	public function geocoder_address($address) 
	{
		return $this->call("geocoder", array("address" => $address, "output" => "json"));
	}
	
	public function geocoder_location($location) 
	{
		return $this->call("geocoder", array("location" => $location, "output" => "json"));
	}
	
	//////////////////////////////////////////////////////////
	// private mathods
	//////////////////////////////////////////////////////////
	
    protected function call($method, $params = array())
    {
    	$params = array_merge($this->auth_params, $params);
		$url = $this->api_server_url . "$method?".http_build_query($params);
		
		if (DEBUG_MODE)
		{
			echo "REQUEST: $url" . "\n";
		}
		
    	$ch = curl_init();
		
		$this_header = array("content-type: text/javascript;charset=utf-8", "Accept-Charset:GBK,utf-8");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     	$data = curl_exec($ch);
		//$data = mb_convert_encoding($data, "GBK", "UTF-8");
    	curl_close($ch);    
    	
		$result = null;
		if (!empty($data))
		{
			if (DEBUG_MODE)
			{
				echo "RETURN: " . $data . "\n";
			}
			$result = $data;//json_decode($data);
		}
		
		return $result;

    }
}
?>
