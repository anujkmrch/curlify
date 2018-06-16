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

	var $files = [];

	# is request a post request or get request
	var $post = false;
	var $head = false;
	var $put = false;

	var $delete = false;
	var $trace = false;
	var $connect = false;

	var $secure = false;
	var $verbose = false;

	var $debug = false;

	var $handleRedirect = false;

	var $optHeader = [];

	/**
	 * Set UserAgent for your curl agent
	 * @author Anuj Kumar
	 * @param string $userAgent
	 */
	function setUserAgent($userAgent)
	{
		$this->userAgent = $userAgent;
	}

	/**
	 * Enable/ Disable Debugging
	 * @author Anuj Kumar
	 */
	function isDebug()
	{
		$this->debug = !$this->debug;
	}

	/**
	 * Enable/ Disable post Variable
	 * @author Anuj Kumar
	 */
	function isPost()
	{
		$this->post = ! $this->post;
	}

	/**
	 * Return current url for the curlify object
	 * @author Anuj Kumar
	 */
	function getUrl()
	{
		return $this->url;
	}

	/**
	 * Set current url to be requested
	 * @author Anuj Kumar
	 * @param string $url
	 */
	function setUrl($url){
		$this->url = $url;
	}

	/**
	 * Insert the data to be posted with the url as get or post data
	 * @author Anuj Kumar
	 * @param string $key data key to be recognized at server side.
	 * @param string $value data value corresponding to the key
	 * @param string $subkey (optional) for set new key for array for sub array
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
	 * @author Anuj Kumar
	 * @param string $key data key to be recognized at server side.
	 * @param string $path file path to be uploaded
	 * @param string $subkey (optional) for set new key for array for sub array
	 */
	function addFile($key,$path,$subkey=null)
	{
		if (file_exists($path)):
			# set method type = post,
			# if method = get or something else
			if (!$this->post)
				$this->post = true;

			if(array_key_exists($key,$this->files)):
				if (!is_array($this->files[$key])):
					$temp = $this->files[$key];
					$this->files[$key] = [];
					$this->files[$key][] = $temp;
				endif;
				$this->files[$key][] = new CurlFile(realpath($path),mime_content_type(realpath($path)));
			else:
				$this->files[$key] = new CurlFile(realpath($path),mime_content_type(realpath($path)));
			endif;
		endif;
	}
	/**
	 * Remove files from the file list, and tells whether files removed
	 * @author Anuj Kumar
	 * @param string $key data key to be recognized at server side.
	 * @return boolean true/false
	 */
	function removeFile($key)
	{
		if(array_key_exists($key, $this->files)){
			unset($this->files[$key]);
			return true;
		}
		return false;
	}

	/**
	 * return the data to be sent to the post
	 * @author Anuj Kumar
	 * @param string $key data key to be recognized at server side.
	 * @return array
	 */
	function getData($key = null)
	{
		if($key and array_key_exists($key,$this->data)):
			return $this->data[$key];
		elseif (!$key and !is_null($key)):
			return false;
		endif;
		return $this->data;
	}

	/**
	 * Method which verifies whether the current url is a valid url or not
	 * before making request
	 * @author Anuj Kumar
	 * @param string $key data key to be recognized at server side.
	 * @return boolean true/false
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
	 * @author Anuj Kumar
	 * @param string $key data key to be recognized at server side.
	 * @return boolean true/false
	 */
	function buildRequestUrl()
	{
		$parts = parse_url($this->url);

		if($this->secure and substr(strtolower($parts['scheme']),-1) != 's')
			$parts["scheme"] = $parts["scheme"]."s";

		if(array_key_exists('query', $parts)):
			parse_str($parts["query"],$query);
			$this->data = array_merge($this->data,$query);
		endif;

		$url = $parts["scheme"]."://".$parts["host"];
		$url .= isset($parts["port"]) ? ':'.$parts["port"] : '';
		$url .= isset($parts["path"]) ? $parts["path"] : '';
		$url .= count($this->data) && !$this->post ? '?'.http_build_query($this->data) : '';
		$url .= isset($parts["fragment"]) ? '#'.$parts["fragment"] : '';
		return urldecode($url);
	}

	/**
	 * Build flatten data for post request
	 * @author Anuj Kumar
	 * @param string $prefix prefix for flatten key, using for subkey
	 * @return boolean true/false
	 */
	function buildPostData($data,$prefix=null) {
		$tree = [];
		foreach($data as $key => $element) {
			if (is_array($element)) {
				$tree = array_merge($tree, $this->buildPostData($element,$prefix = $key));
			} else {
				$tree[$prefix.'['.$key.']'] = $element;
			}
		}
		return $tree;
	}

	/**
	 * Build flatten file path for post request
	 * @author Anuj Kumar
	 * @param array $data
	 * @param string $prefix prefix for flatten key, using for subkey
	 * @return boolean true/false
	 */
	function buildPostFiles($data,$prefix=null) {
		$tree = [];
		foreach($data as $key => $element) {
			if (is_array($element)) {
				$tree = array_merge($tree, $this->buildPostFiles($element,$prefix = $key));
			} else {
				if($prefix)
					$tree[$prefix.'['.$key.']'] = $element;
				else
					$tree[$key] = $element;
			}
		}
		return $tree;
	}
	
	/**
	 * Arrange all the elements together and make a request to the server
	 * @author Anuj Kumar
	 * @param boolean $raw to suggest raw result or processed
	 * @param boolean $sortHeader convert header text into the 
	 * @return array/text ['header','body',info] / raw response from server text
	 */
	function requestNow($raw = false,$sortHeader = false)
	{
		if ($this->url && $this->verifyUrl()):
			$request = curl_init();
			curl_setopt_array($request, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_HEADER=> 1,
			    CURLOPT_VERBOSE=> $this->verbose,
			    CURLOPT_USERAGENT => $this->userAgent,
			    CURLOPT_URL => $this->buildRequestUrl(),
			));

			if($this->handleRedirect)
				curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

			#check if the post request
			if ($this->post):
				curl_setopt($request, CURLOPT_POST, 1);
				if(count($this->data) or count($this->files))
					$postData = array_merge($this->buildPostData($this->data),$this->buildPostFiles($this->files));
					devel_logging($postData);
					curl_setopt($request,CURLOPT_POSTFIELDS,$postData);
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