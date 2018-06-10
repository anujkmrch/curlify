<?php
/**
 * Logging function to print
 */
function devel_logging($log){
	print_r($log);
}

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
	var $isConnect = false;

	var $isSecure = false;
	var $isVerbose = false;
	
	var $debug = false;

	function isDebug()
	{
		$this->debug = !$this->debug;
	}

	/**
	 * get the current url
	 */
	function getUrl()
	{
		return $this->url;
	}

	/**
	  * set the current url
	  */
	function setUrl($url){
		$this->url = $url;
	}

	/**
	 * Create the data to be posted with the url or post data
	 */
	function setData($key,$value,$subkey=null)
	{
		if(array_key_exists($key,$this->data)):
			if (!is_array($this->data[$key])):
				$temp = $this->data[$key];
				$this->data[$key] = [];
				$this->data[$key][] = $temp;
			endif;
			
			if($subkey)
				$this->data[$key][$subkey] = $value;
			else
				$this->data[$key][] = $value;
		else:
			$this->data[$key] = $value;
		endif;
	}
	/**
	 * Add file to be posted with the post data
	 */
	function addFile($key,$path,$subkey=null)
	{
		# set method type = post, 
		# if method = get or something else
		if (!$this->isPost)
			$this->isPost = true;

		if(array_key_exists($key,$this->files)):
			if (!is_array($this->files[$key])):
				$temp = $this->files[$key];
				$this->files[$key] = [];
				$this->files[$key][] = $temp;
			endif;
			if($subkey)
				$this->data[$key][$subkey] = $path;
			else
				$this->data[$key][] = $path;
		else:
			$this->files[$key] = $path;
		endif;
	}

	function removeFile($key,$removeByPath)
	{

	}

	/**
	 * return the data to be sent to the post
	 */
	function getData($key = null)
	{
		if($key and array_key_exists($key,$this->data)):	
			return $this->data[$key];
		elseif (!$key):
			return false;
		endif;
		return $this->data;
	}

	/**
	 * Method which verifies whether the current url is a valid url or not
	 * before making request
	 */
	function verifyUrl()
	{
		$parts = parse_url($this->url);
		if(array_key_exists("scheme",$parts) && array_key_exists("host",$parts)):
			return true;
		endif;
		return false;
	}

	/**
	 * Method to create url component from the data
	 */
	function buildRequestUrl()
	{
		$parts = parse_url($this->url);

		if($this->isSecure and substr(strtolower($parts['scheme']),-1) != 's')
			$parts["scheme"] = $parts["scheme"]."s";

		if(array_key_exists('query', $parts)):
			parse_str($parts["query"],$query);
			$this->data = array_merge($this->data,$query);
		endif;
		
		$url = $parts["scheme"]."://".$parts["host"];
		$url .= isset($parts["path"]) ? $parts["path"] : '';
		$url .= count($this->data) && !$this->isPost ? '?'.http_build_query($this->data) : '';
		$url .= isset($parts["fragment"]) ? '#'.$parts["fragment"] : '';

		return urldecode($url);
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

			#check if the post request
			if ($this->isPost):
				curl_setopt($request, CURLOPT_POST, 1);
				if(count($this->data) or count($this->files))
					curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($this->data));
			endif;

			if (!$response = curl_exec($request)):
				if($this->debug)
					devel_logging('Error: "' . curl_error($request) . '" - Code: ' . curl_errno($request)."\n");
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
				$headers = [];
				$status = array_shift($lines);
				$headers["Status"] = $status;
				$headers["Code"] = $info["http_code"];
				foreach ($lines as $line):
			        list ($key, $value) = explode(': ', $line);
			        $headers[$key] = $value;
			    endforeach;
				if($this->debug){
					devel_logging($headers);
				}
			endif;
			return ['headers'=>$headers,'body'=>$body,'info'=>$info];
		else:
			if($this->debug == 1)
				devel_logging("invalid url or url not set yet\n");
		return false;
		endif;
	}
}
?>