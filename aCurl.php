<?php


namespace aCurl {

	class aCurl
	{
		const VERSION = 2.0;
		const HTTP_GET = 1;
		const HTTP_POST = 2;
		const HTTP_HEAD = 3;

		protected $_userAgent = "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36";
		protected $_url;
		protected $_timeout = 10;
		protected $_maxRedirects = 4;
		protected $_cookies = array();
		protected $_method = self::HTTP_GET;
		protected $_postFields;
		protected $_referrer = "";
		protected $_requestHeaders = array('Accept:');
		protected $_verifyPeer = true;

		protected $_webpage;
		protected $_status = 0;
		protected $_info;
//		public $authentication = false;
//		public $auth_name = '';
//		public $auth_pass = '';

		protected $_responseBody = "";
		protected $_responseHeaders = "";

		protected $_hasBeenExecuted = false;

//		public function useAuth($use = true)
//		{
//			$this->authentication = false;
//			if ($use == true) $this->authentication = true;
//		}
//
//		public function setName($name)
//		{
//			$this->auth_name = $name;
//		}
//
//		public function setPass($pass)
//		{
//			$this->auth_pass = $pass;
//		}

		public function __construct($url, $cookiesArray = null)
		{
			$this->_url = $url;
			if (isset($cookiesArray)) {
				$this->_cookies &= $cookiesArray;
			}
		}

		public function addRequestHeader($header)
		{
			$this->_requestHeaders[] = $header;
		}

		public function maxRedirects($maxRedirects)
		{
			$this->_maxRedirects = (int)$maxRedirects;
		}

		public function setReferrer($referrer)
		{
			$this->_referrer = $referrer;
		}

		public function setPost($postFields)
		{
			$this->_method = self::HTTP_POST;
			$this->_postFields = $postFields;
		}

		public function setUserAgent($userAgent)
		{
			$this->_userAgent = $userAgent;
		}

		public function setVerifyPeer($verifyPeer = true)
		{
			$this->_verifyPeer = !!$verifyPeer;
		}

		public function setMethod($method = self::HTTP_GET)
		{
			$this->_method = (int)$method;
		}

		public function getHttpStatus()
		{
			return $this->_status;
		}

		public function getInfo()
		{  //  See http://www.php.net/manual/en/function.curl-getinfo.php#refsect1-function.curl-getinfo-returnvalues for the array that is returned.
			return $this->_info;
		}

		public function getResponseHeaders()
		{
			if (!$this->_hasBeenExecuted) {
				$this->execute();
			}
			return (string)$this->_responseHeaders;
		}

		public function execute()
		{
			if ($this->_hasBeenExecuted) {
				throw new aCurlException("aCurl has already been executed.");
			}

			$s = curl_init();

			/* general options */
			curl_setopt($s, CURLOPT_SSL_VERIFYPEER, $this->_verifyPeer);
			curl_setopt($s, CURLOPT_URL, $this->_url);
			curl_setopt($s, CURLOPT_HTTPHEADER, $this->_requestHeaders);
			curl_setopt($s, CURLOPT_TIMEOUT, $this->_timeout);
			curl_setopt($s, CURLOPT_MAXREDIRS, $this->_maxRedirects);
			curl_setopt($s, CURLOPT_FOLLOWLOCATION, true);
//			curl_setopt($s, CURLOPT_COOKIEJAR, $this->_cookieFileLocation);
//			curl_setopt($s, CURLOPT_COOKIEFILE, $this->_cookieFileLocation);
			curl_setopt($s, CURLOPT_VERBOSE, true);
			curl_setopt($s, CURLOPT_FORBID_REUSE, true);
			curl_setopt($s, CURLOPT_RETURNTRANSFER, true); // returns the result, rather than outputting it.
			curl_setopt($s, CURLOPT_USERAGENT, $this->_userAgent);
			curl_setopt($s, CURLOPT_REFERER, $this->_referrer);

//			if ($this->authentication == true) {
//				curl_setopt($s, CURLOPT_USERPWD, $this->auth_name . ':' . $this->auth_pass);
//			}

			/* HTTP Mode settings */
			if ($this->_method === self::HTTP_POST) {
				curl_setopt($s, CURLOPT_POST, true);
				curl_setopt($s, CURLOPT_POSTFIELDS, $this->_postFields);
				curl_setopt($s, CURLOPT_HTTPGET, false);
			} elseif ($this->_method === self::HTTP_HEAD) {
				curl_setopt($s, CURLOPT_NOBODY, true);
			}

			/* Cookie Management */
			curl_setopt($s, CURLOPT_HEADER, true);    // includes headers in the response.
			curl_setopt($s, CURLINFO_HEADER_OUT, true); // Makes outgoing headers available from info call.

			/* Execute */
			$response = curl_exec($s);

			/* Parse Results */
			if ($response === false) {
				trigger_error("cURL failed.", E_USER_WARNING);  // in case cURL isn't cooperating.
			}
			$this->_status = curl_getinfo($s, CURLINFO_HTTP_CODE);
			$this->_info = curl_getinfo($s);
			$this->_url = curl_getinfo($s, CURLINFO_EFFECTIVE_URL);

			$header_size = curl_getinfo($s, CURLINFO_HEADER_SIZE);
			$this->_responseHeaders = substr($response, 0, $header_size);
			$this->_responseBody = substr($response, $header_size);

			$this->_webpage = $response;

			curl_close($s);
		}

		public function __tostring()
		{
			return $this->getResponseBody();
		}

		/**
		 * @return string
		 * @throws aCurlException
		 */
		public function getResponseBody()
		{
			if (!$this->_hasBeenExecuted) {
				$this->execute();
			}
			return (string)$this->_responseBody;
		}
	}

	class aCurlCookie
	{
		const EXPIRES_SESSION = 0;
		/** @var null|\DateTime  Current Time for comparison for expiry, as needed. */
		private static $now = null;
		/** @var string The domain with which the cookie is associated. */
		public $domain = "";
		/** @var string The restricting path for the domain.  */
		public $path = "";
		/** @var bool Whether the cookie should have the HTTP-Only flag associated with it. */
		public $httpOnly = false;
		/** @var \DateTime|self::EXPIRES_SESSION When the cookie expires. */
		public $expires = self::EXPIRES_SESSION;
		/** @var string|null The name of the cookie. */
		public $name = null;
		/** @var string The value of the cookie. */
		public $value = "";

		/**
		 * Creates a generic cookie object or a cookie object based on a Set-Cookie response header string.  To create
		 * from a string, both the set-cookie string and the domain must be provided.
		 *
		 * @param String|null $setCookieString The string that's passed after a Set-Cookie header.
		 * @param String|null $domain The domain of the request, for when domain is not defined in the set-cookie string.
		 *
		 * @throws aCurlException You must provide both or neither setCookieString and domain.
		 */
		public function __construct($setCookieString = null, $domain = null)
		{
			if (isset($setCookieString) XOR isset($domain)) {
				throw new aCurlException("You must provide both or neither setCookieString and domain.");
			}

			if (!isset($setCookieString)) {
				return;
			}

			$this->domain = $domain; // gets over-written below if it's in the set-cookie string

			$ckAttribs = explode("; ", $setCookieString);

			/* Pull cookie name and value */
			$ckAttrib = array_shift($ckAttribs);
			$ckAttrib = trim($ckAttrib);
			$ckAttrib = explode("=", $ckAttrib, 2);
			$this->name = $ckAttrib[0];
			if (isset($ckAttrib[1])) {
				$this->value = $ckAttrib[1];
			}

			/* Other Cookie Attributes */
			foreach ($ckAttribs as $ckAttrib) {
				$ckAttrib = trim($ckAttrib);
				$ckAttrib = explode("=", $ckAttrib, 2);
				switch (trim(strtolower($ckAttrib[0]))) {
					case 'path':
						$this->path = $ckAttrib[1];
						break;

					case 'domain':
						$this->domain = $ckAttrib[1];
						break;

					case "httponly":
						$this->httpOnly = true;
						break;

					case 'expires':
						$this->expires = new \DateTime($ckAttrib[1]);
						break;
				}
			}
		}

		/**
		 * Determines whether the cookie has expired.
		 *
		 * @return bool True means 'Expired'
		 * @throws aCurlException
		 */
		public function isExpired() {
			if ($this->name === null) {
				return true;
			}

			if ($this->expires === self::EXPIRES_SESSION) {
				return false;
			}

			if (!($this->expires instanceof \DateTime)) {
				throw new aCurlException("Expiration is of invalid type.  It must be either DateTime object, or the EXPIRES_SESSION constant");
			}

			self::initNow();
			return (self::$now <= $this->expires);
		}

		/**
		 * Initializes the 'now' var to a DateTime object for expiration comparisons.
		 */
		private static function initNow() {
			if (!isset(self::$now)) {
				self::$now = new \DateTime();
			}
		}
	}

	class aCurlException extends \Exception {}

	class aCurlCookieJar extends \ArrayObject {

	}


}
