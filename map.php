<?php
require_once(__DIR__ . "/BaiduMapClient.php");

$api_key = "0e4fde2b4acbc043abdb68df511359ae";
// initialize client object
$api = new BaiduMapClient($api_key);

$api->place_search("百度大厦", "39.915,116.404", "2000");

$api->geocoder_address("百度大厦");

$api->geocoder_location("39.915,116.404,39.975,116.414");

?>