<?php

require_once(__DIR__ . "/BaiduMapClient.php");
require_once(__DIR__ . "/BaiduTranslateClient.php");
/**
  * wechat php test
  */

//define your token
define("TOKEN", "hackfisher");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];
		
        //valid signature , option
        if($this->checkSignature()){
			echo $echoStr;
        } else {
			exit;
		}
    }

    public function responseMsg()
    {

		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
				$msgType = $postObj->MsgType;
				if ($msgType=="text") {
					$keyword = trim($postObj->Content);
					$time = time();
					$textTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[%s]]></MsgType>
								<Content><![CDATA[%s]]></Content>
								<FuncFlag>0</FuncFlag>
								</xml>";             
					if(!empty( $keyword ))
					{
						$api_key = "SEwcXuDQE7ZcGM0Fxz2B02zb";
						// initialize client object
						$api = new BaiduTranslateClient($api_key);
						$result = $api->translate($keyword, "auto", "auto");
						$contentStr = "Translation Failed!";
						if (!empty($result->trans_result)) {
							if (count($result->trans_result) > 0) {
								$contentStr = $result->trans_result[0]->dst;
							}
						}
						$msgType = "text";
						//$contentStr = "Hi, I'm Mars Robot! 有何贵干? 你可以发送位置，试试看。";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}else{
						echo "Input something...";
					}
				} else if ($msgType=="location"){
					$location_x = $postObj->Location_X;
					$location_y = $postObj->Location_Y;
					$scale = $postObj->Scale;
					$label = $postObj->Label;
					$time = time();
					$textTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[%s]]></MsgType>
								<Content><![CDATA[%s]]></Content>
								<FuncFlag>0</FuncFlag>
								</xml>";
					$api_key = "0e4fde2b4acbc043abdb68df511359ae";
					// initialize client object
					$api = new BaiduMapClient($api_key);
					$result = $api->geocoder_location($location_x.",".$location_y);
					if (!empty($result)) {
						$msgType = "text";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $label . $result);
						echo $resultStr;
					}
				} else if ($msgType=="image"){
					$url = $postObj->PicUrl;
					$time = time();
					$textTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[%s]]></MsgType>
								<Content><![CDATA[%s]]></Content>
								<FuncFlag>0</FuncFlag>
								</xml>";
					$msgType = "text";
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $url);
					echo $resultStr;
				} else {
					echo "";
					exit;
				}
        }else {
        	echo "";
        	exit;
        }
    }
		

	private function debug($text) {
		$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
		$fp = fopen($DOCUMENT_ROOT."/debug.txt",'ab');
		$tab = "--------------";
		fwrite($fp, $text, strlen($text));
		fwrite($fp, $tab, strlen($tab));
		fclose($fp);
	}
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	

		$this->debug($signature);
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}
?>
