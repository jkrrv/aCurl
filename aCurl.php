<?php 
 class aCurl { 
	protected $_useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36'; 
	protected $_url; 
	protected $_followlocation; 
	protected $_timeout; 
	protected $_maxRedirects; 
	protected $_cookieFileLocation = './cookie.txt'; 
	protected $_post; 
	protected $_postFields; 
	protected $_referer =""; 

	protected $_session; 
	protected $_webpage; 
	protected $_includeHeader; 
	protected $_noBody; 
	protected $_status; 
	protected $_info; 
	protected $_binaryTransfer; 
	public    $authentication = 0; 
	public    $auth_name = ''; 
	public    $auth_pass = ''; 

	public function useAuth($use){ 
		$this->authentication = 0; 
		if($use == true) $this->authentication = 1; 
	} 

	public function setName($name){ 
		$this->auth_name = $name; 
	} 
	public function setPass($pass){ 
		$this->auth_pass = $pass; 
	} 

	public function __construct($url,$followlocation = true,$timeOut = 10,$maxRedirecs = 4,$includeHeader = true,$noBody = false) 
	{ 
		$this->_url = $url; 
		$this->_followlocation = $followlocation; 
		$this->_timeout = $timeOut; 
		$this->_maxRedirects = $maxRedirecs; 
		$this->_noBody = $noBody; 
		$this->_includeHeader = $includeHeader; 

		$this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt'; 
	} 
	
	public function includeHeader($something) { // Kurtz addition
		$this->_includeHeader=$something;
	}
	
	public function maxRedirects($something) { // Kurtz addition
		$this->_maxRedirects=$something;
	}

	public function setReferer($referer){ 
		$this->_referer = $referer; 
	} 

	public function setCookieFile($path) 
	{ 
		$this->_cookieFileLocation = $path; 
	} 

	public function setPost($postFields) 
	{ 
		$this->_post = true; 
		$this->_postFields = $postFields; 
	} 

	public function setUserAgent($userAgent) 
	{ 
		$this->_useragent = $userAgent; 
	} 

	public function createCurl($url = 'nul') 
	{ 
		if($url != 'nul'){ 
			$this->_url = $url; 
		} 

		$s = curl_init(); 

		curl_setopt($s,CURLOPT_URL,$this->_url); 
		curl_setopt($s,CURLOPT_HTTPHEADER,array('Accept:'));
		curl_setopt($s,CURLOPT_TIMEOUT,$this->_timeout); 
		curl_setopt($s,CURLOPT_MAXREDIRS,$this->_maxRedirects); 
		curl_setopt($s,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($s,CURLOPT_FOLLOWLOCATION,$this->_followlocation); 
		curl_setopt($s,CURLOPT_COOKIEJAR,$this->_cookieFileLocation); 
		curl_setopt($s,CURLOPT_COOKIEFILE,$this->_cookieFileLocation); 
		curl_setopt($s,CURLOPT_VERBOSE,true);
		curl_setopt($s,CURLOPT_FORBID_REUSE,true);		

		if($this->authentication == 1){ 
			curl_setopt($s, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass); 
		} 
		
		if($this->_post) 
		{ 
			curl_setopt($s,CURLOPT_POST,true); 
			curl_setopt($s,CURLOPT_POSTFIELDS,$this->_postFields); 
			curl_setopt($s,CURLOPT_HTTPGET,false); 
		} 

		if($this->_includeHeader) 
		{ 
			curl_setopt($s,CURLOPT_HEADER,true);
			curl_setopt($s,CURLINFO_HEADER_OUT,true);
		} 

		if($this->_noBody) 
		{
			curl_setopt($s,CURLOPT_NOBODY,true); 
		} 
		
		/* not happy
		if($this->_binary) 
		{ 
			curl_setopt($s,CURLOPT_BINARYTRANSFER,true); 
		} 
		*/ 
		
		curl_setopt($s,CURLOPT_USERAGENT,$this->_useragent); 
		curl_setopt($s,CURLOPT_REFERER,$this->_referer); 

		$this->_webpage = curl_exec($s); 
		$this->_status = curl_getinfo($s,CURLINFO_HTTP_CODE); 
		$this->_info = curl_getinfo($s); // Kurtz addition
		curl_close($s); 
	} 

	public function getHttpStatus() { 
		return $this->_status; 
	} 
	
	public function getInfo() {  // Kurtz addition.  See http://www.php.net/manual/en/function.curl-getinfo.php#refsect1-function.curl-getinfo-returnvalues for the array that is returned. 
		return $this->_info; 
	} 

	public function __tostring(){ 
		return (string) $this->_webpage; 
	} 
} 
?>