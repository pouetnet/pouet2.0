<?php
/**
 * Basic connection library for SceneId 2.0
 * @author Reza Esmaili <me@dfox.info>
 * @version 1.2
 *
 * @example
 *  $data = SceneId::Factory('test', 'test')->getUserInfoByUserId(1)->asXML();
 *  $data = SceneId::Factory('test', 'test')->getUserInfoByUserLogin('dfox')->asSimpleXML();
 */

class SceneIdException extends Exception {}

class SceneId {

	private $login;
	private $password;
	private $url = 'https://id.scene.org/sceneid.php';
	private $encoding = 'utf-8';
	private $method = 'POST';
	private $format = 'xml';
	private $command;
	private $commandParams = array();

	/**
	 * Factory
	 * @param string $login the sceneid portal login
	 * @param string $password the sceneid portal password
	 * @param string $url url to the sceneid portal
	 * @return new SceneId
	 */
	public static function factory($login, $password, $url=NULL, $encoding=NULL, $method=NULL) {
		return new SceneID($login, $password, $url, $encoding, $method);
	}

	/**
	 * Basic constructor
	 * @param string $login the sceneid portal login
	 * @param string $password the sceneid portal password
	 * @param string $url url to the sceneid portal
	 * @return self
	 */
	public function __construct($login, $password, $url=NULL, $encoding=NULL, $method=NULL)
	{
		// Check if all conditions are met
		if (empty($login)) {
			throw new SceneIdException('Login empty');
		}
		if (empty($password)) {
			throw new SceneIdException('Password empty');
		}
		if (!is_null($url) && empty($url)) {
			throw new SceneIdException('URL empty');
		}
		if (!is_null($encoding) && empty($encoding)) {
			throw new SceneIdException('Encoding empty');
		}
		if (!is_null($method) && empty($method)) {
			throw new SceneIdException('Method empty');
		}

		// Assign class variables
		$this->login = $login;
		$this->password = md5($password);
		if ($url) {
			$this->url = $url;
		}
		if ($encoding) {
			$this->encoding = $encoding;
		}
		if ($method) {
			$this->method = $method;
		}
	}

	/**
	 * Request data from sceneid and return the result
	 * @return raw data returned from sceneid
	 */
	private function getData()
	{
		$params = array(
			'portalLogin' => $this->login,
			'portalPassword' => $this->password,
			'command' => $this->command
		);

		// GET is deprecated and will be removed in the future
		switch ($this->method) {
			case 'GET':
				$url = $this->url.'?'.http_build_query(array_merge(array('encoding' => $this->encoding, 'format' => $this->format), $params, $this->commandParams));
				$content = NULL;
				break;
			case 'POST':
				$url = $this->url.'?'.http_build_query(array('encoding' => $this->encoding, 'format' => $this->format));
				$content = http_build_query(array_merge($params, $this->commandParams));
				break;
		}

		// Create stream context
		$options = array('http' => array('method' => $this->method, 'content' => $content, 'header' => 'Content-Type: application/x-www-form-urlencoded'), 'ssl'=>array('verify_peer' => false));
		$context = stream_context_create($options);

		// Open the connection with the given stream context
		$connection = fopen($url, 'rb', false, $context);
		if (!$connection) {
			throw new SceneIdException('Could not open connection');
		}

		// Receive data
		$data = '';
		while (!feof($connection)) {
			$data .= fgets($connection, 4096);
		}

		// Close socket
		fclose($connection);

		return $data;
	}

	/**
	 * Return requested data as raw xml
	 * @return string
	 */
	public function asXML()
	{
		return $this->asData();
	}

	/**
	 * Return requested data as it is
	 * @return string
	 */
	public function asData()
	{
		if ($this->command) {
			return $this->getData();
		}
	}

	/**
	 * Return requested data as simplexml object
	 * @return SimpleXML object
	 */
	public function asSimpleXml()
	{
		if ($this->command) {
			$out = @simplexml_load_string($this->getData());
			if ($out)
			  return $out;
			else
			  throw new SceneIdException('Error in XML: '.$this->getData());
		}
	}

  private function XML2Array($xml)
  {
    foreach($xml->children() as $key=>$value)
    {
      if (count($value->children()) == 0 || (count($value->children()) == 1 && count($value->attributes()) > 0))
        $out[$key] = (string)$value;
      else
        $out[$key] = $this->XML2Array($value);
    }
    return $out;
  }
	public function asAssoc()
	{
	  if ($this->format == "xml")
	  {
	    $data = $this->XML2Array( $this->asSimpleXml() );
	  }
	  elseif ($this->format == "json")
	  {
	    $data = json_decode( $this->asData(), true );
	  }
	  else
	  {
	    $data = $this->asData();
	  }
	  return $data;
	}


	/**
	 * Get user info by user id
	 * @param string $userId the sceneid user id
	 * @return $this
	 */
	function getUserInfoById($userID)
	{
		$this->command = 'getUserInfo';
		$this->commandParams = array('userID' => $userID);
		return $this;
	}

	/**
	 * Get user info by user login
	 * @param string $login the sceneid user login
	 * @return $this
	 */
	function getUserInfoByLogin($login)
	{
		$this->command = 'getUserInfo';
		$this->commandParams = array('login' => $login);
		return $this;
	}

	/**
	 * Get user info by user login
	 * @param string $cookie the sceneid login cookie
	 * @return $this
	 */
	function getUserInfoByCookie($cookie)
	{
		$this->command = 'getUserInfo';
		$this->commandParams = array('cookie' => $cookie);
		return $this;
	}

	/**
	 * Get portal list
	 * @return $this
	 */
	function getPortalList()
	{
		$this->command = 'getPortalList';
		$this->commandParams = array();
		return $this;
	}

	/**
	 * Login user
	 * @param string $login the sceneid user login
	 * @param string $password the sceneid user password
	 * @param string $externalid an external id to match different databases (mostly unused)
	 * @param string $permanent flag to set the login to be permanent (NOT SURE)
	 * @return $this
	 */
	function login($login, $password, $externalid=NULL, $permanent=NULL)
	{
		$params = array('login' => $login, 'password' => md5($password), 'externalid' => $externalid, 'permanent' => $permanent, 'ip' => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'));

		$this->command = 'loginUserMD5';
		$this->commandParams = $params;
		return $this;
	}

	// TODO rename logout methods to sound better
	/**
	 * Logout user by userId
	 * @param string $userID the sceneid user id
	 * @return $this
	 */
	function logoutById($userID)
	{
		$this->command = 'logoutUser';
		$this->commandParams = array('userID' => $userID);
		return $this;
	}

	/**
	 * Logout user by login
	 * @param string $login the sceneid user login
	 * @return $this
	 */
	function logoutByLogin($login)
	{
		$this->command = 'logoutUser';
		$this->commandParams = array('login' => $login);
		return $this;
	}

	/**
	 * Logout user by cookie
	 * @param string $cookie the sceneid login cookie
	 * @return $this
	 */
	function logoutByCookie($cookie)
	{
		$this->command = 'logoutUser';
		$this->commandParams = array('cookie' => $cookie);
		return $this;
	}

	/**
	 * Request new user password by userId
	 * @param string $userID the sceneid user id
	 * @return $this
	 */
	function requestPasswordById($userID)
	{
		$this->command = 'requestNewUserPassword';
		$this->commandParams = array('userID' => $userID);
		return $this;
	}

	/**
	 * Request new user password by login
	 * @param string $login the sceneid user login
	 * @return $this
	 */
	function requestPasswordByLogin($login)
	{
		$this->command = 'requestNewUserPassword';
		$this->commandParams = array('login' => $login);
		return $this;
	}

	/**
	 * Get file info from scene.org
	 * @param int $fileID scene.org file id
	 * @return $this
	 */
	function getFileInfo($fileID)
	{
		$this->command = 'getFileInfo';
		$this->commandParams = array('fileID' => $fileID);
		return $this;
	}

	/**
	 * Set user info
	 * @param string $userID the sceneid user id
	 * @param array $params an array full of params
	 * @return $this
	 */
	function setUserInfo($userID, $params)
	{
		// Filter illegal parameters
		$allowed = array('userID', 'nickname', 'firstname', 'lastname', 'email', 'url', 'password', 'password2', 'showinfo', 'birthdate', 'country');
		foreach ($params as $param => $value) {
			if (!in_array($param, $allowed)) {
				unset($params[$param]);
			}
		}

		// Check valid date
		if (isset($params['birthdate'])) {
			if (!preg_match('/^(\d\d\d\d)-(\d\d?)-(\d\d?)$/', $params['birthdate'], $matches)) {
				throw new SceneIdException('Invalid date');
			}
		}

		// Ensure 0 or 1
		if (isset($params['showinfo'])) {
			$params['showinfo'] = ($params['showinfo'] ? 1 : 0);
		}

		// Only update passwords if they a) are provided and b) match
		if (isset($params['password']) && isset($params['password2'])) {
			if (strlen(trim($params['password'])) > 0 && $params['password'] == $params['password2']) {
				$params['password']   = md5($params['password']);
				$params['password2']  = md5($params['password2']);
			}
		}

		// Add userid
		$params = array_merge(array('userID' => $userID), $params);

		$this->command = 'setUserInfoMD5';
		$this->commandParams = $params;
		return $this;
	}

	/**
	 * Register user
	 * @param array $params an array full of params
	 * @return $this
	 */
	function registerUser($params)
	{
		// Filter illegal parameters
		$allowed = array('login', 'nickname', 'firstname', 'lastname', 'email', 'url', 'password', 'password2', 'showinfo', 'birthdate', 'country');
		foreach ($params as $param => $value) {
			if (!in_array($param, $allowed)) {
				unset($params[$param]);
			}
		}

		// Check valid date
		if (isset($params['birthdate'])) {
			if (!preg_match('/^(\d\d\d\d)-(\d\d?)-(\d\d?)$/', $params['birthdate'], $matches)) {
				throw new SceneIdException('Invalid date');
			}
		}

		// Ensure 0 or 1
		if (isset($params['showinfo'])) {
			$params['showinfo'] = ($params['showinfo'] ? 1 : 0);
		}

		// Only valid if passwords a) are provided and b) match
		if (isset($params['password']) && isset($params['password2'])) {
			if (strlen(trim($params['password'])) > 0 && $params['password'] == $params['password2']) {
				$params['password']   = md5($params['password']);
				$params['password2']  = md5($params['password2']);
			} else {
				throw new SceneIdException('No passwords provided or passwords don\'t match');
			}
		}

		$this->command = 'registerUserMD5';
		$this->commandParams = $params;
		return $this;
	}
}
