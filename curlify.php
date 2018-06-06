<?php
/**
 * Curl Class for executing the curl object
 */
class Curlify
{
	#request url
	var $url = null;
	
	#User agent
	var $userAgent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.62 Safari/537.36";
	
	# Reqeust parameters
	var $data = [];

	# is request a post request or get request
	var $isPost = false;
	var $isHead = false;
	var $isPut = false;
	var $isDelete = false;
	var $isTrace = false;
	var $isConect = false;

	var $isSecure = false;
	
	var $isVerbose = false;
	function getUrl()
	{
		return $this->url;
	}

	function setUrl($url){
		$this->url = $url;
	}

	function verifyUrl()
	{
		return true;
	}
	/**
	*/
	function buildRequestUrl()
	{
		return $this->url;
	}
	

	/**
	 *
	 */
	function requestNow($raw = false,$sortHeader = false)
	{
		if ($this->url && $this->verifyUrl()):
			$request = curl_init();
			curl_setopt_array($request, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_HEADER=> 1,
			    CURLOPT_VERBOSE=> $this->isVerbose,
			    CURLOPT_USERAGENT => $this->userAgent,
			    CURLOPT_URL => $this->buildRequestUrl(),
			));

			if (!$response = curl_exec($request)):
				print('Error: "' . curl_error($request) . '" - Code: ' . curl_errno($request)."\n");
				return false;
			endif;
			$info = curl_getinfo($request);
			curl_close($request);

			if ($raw){
				return $response;
			}
			#separate header and body
			list($headers, $body) = explode("\r\n\r\n", $response, 2);

			if($sortHeader):
				$lines = explode("\n", $headers);
				$status = array_shift($lines);
				print_r($lines);
			endif;
			return ['headers'=>$headers,'body'=>$body];
		else:
			print "invalid url or url not set yet\n";
		return false;
		endif;
	}
}

?>