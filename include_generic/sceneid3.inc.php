<?php

/**
 * Wrapper library for authenticating and using SceneID 3.0
 * @author Gargaj / Conspiracy <gargaj@scene.org>
 */
 
class SceneID3Exception extends Exception {}
class SceneID3AuthException extends SceneID3Exception {
  public function __construct($message, $code = 0, Exception $previous = null, $dataJSON = "") {
    $data = json_decode($dataJSON);
    if ($data && $data->error_description)
      $message .= ": " . $data->error_description;
    parent::__construct($message, $code, $previous);    
  }
}

class SceneID3OAuth
{
  const ENDPOINT_TOKEN = "http://fakocka.hopto.org/sceneid/3/oauth/token/";
  const ENDPOINT_AUTH = "http://fakocka.hopto.org/sceneid/3/oauth/authorize/";
  const ENDPOINT_RESOURCE ="http://fakocka.hopto.org/sceneid/3/api/3.0";

  protected $clientID = null;
  protected $clientSecret = null;
  protected $redirectURI = null;
  protected $accessToken = null;
  protected $state = null;
  protected $scope = array();
  
  protected $format = "json";

  /**
   * Constructor
   * @param array $options The initializing parameters of the class
   * @return self
   *
   * The following parameters are required in $options:
   *   clientID     - OAuth2 client ID
   *   clientSecret - OAuth2 client secret
   *   redirectURI  - OAuth2 redirect/return URL
   */  
  function __construct( $options = array() )
  {
    if (!function_exists("curl_init"))
      throw new SceneID3Exception("cURL not installed!");
    
    $mandatory = array("clientID","clientSecret","redirectURI");
    foreach($mandatory as $v)
    {
      if (!$options[$v])
        throw new Exception("'".$v."' invalid or missing from initializer array!");
      $this->$v = $options[$v];
    }
    
  }

  /**
   * Send HTTP request
   * @access protected
   * @param string $url The request target URL
   * @param string $method GET, POST, PUT, etc.
   * @param string $contentArray Key-value pairs to be sent in the request body
   * @param string $headerArray HTTP headers to be sent
   * @return string The URL contents
   */  
  protected function Request( $url, $method = "GET", $contentArray = array(), $headerArray = array() )
  {
    $ch = curl_init();

    $a = array();
    foreach($headerArray as $k=>$v) $a[] = $k.": ".$v;

    $getArray  = $method == "GET"  ? $contentArray : array();
    $postArray = $method == "POST" ? $contentArray : array();
    
    $getArray["format"] = $this->format;
    
    if ($getArray)
    {
      $data = http_build_query($contentArray);
      $url .= "?" . $getArray;
    }
    
    if ($postArray)
    {
      $data = http_build_query($contentArray);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    if ($method == "POST")
      curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $a);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
  }

  /**
   * Get access token via client credentials
   * @return boolean 'true' on success
   * @throws SceneID3AuthException Exception is thrown when the data returned
   *   by the endpoint is malformed or the authentication fails.
   *
   * The function authenticates with the OAuth2.0 endpoint using the
   * supplied credentials and stores the returning access token
   */
  function GetClientCredentialsToken()
  {
    $authString = "Basic " . base64_encode( $this->clientID . ":" . $this->clientSecret );

    $params = array("grant_type"=>"client_credentials");
    if ($this->scope)
      $params["scope"] = implode(" ",$this->scope);
    
    $data = $this->Request( self::ENDPOINT_TOKEN, "POST", $params, array("Authorization"=>$authString) );

    $authTokens = json_decode( $data );

    if (!$authTokens || !$authTokens->access_token)
      throw new SceneID3AuthException("Authorization failed", 0, null, $data);

    $this->accessToken = $authTokens->access_token;

    return true;
  }
  
  /**
   * Sets the "state" parameter to avoid CSRF
   * @param string $state The "state" parameter
   *
   * See also: http://tools.ietf.org/html/rfc6749#section-10.12
   */
  function SetState( $state )
  {
    $this->state = $state;
  }
  
  /**
   * Sets the request scope
   * @param array $scope The requested scopes
   */
  function SetScope( $scope )
  {
    if (is_string($scope))
      $scope = preg_split("/\s+/",$scope);
      
    $this->scope = $scope;
  }
  
  /**
   * Sets the communication format
   * @param string $format The communication format - must be either "json" or "xml"
   * @throws SceneID3Exception Throws exception when the format isn't one of the above.
   */
  function SetFormat( $format )
  {
    $format = strtolower($format);
    if (array_search($format,array("json","xml"))===false)
      throw new SceneID3Exception("Format has to be either XML or JSON!");
      
    $this->format = $format;
  }
  
  /**
   * Sets authentication token manually
   * @param string $accessToken The access token
   */
  function SetAuthToken( $accessToken = null )
  {
    $this->accessToken = $accessToken;
  }

  /**
   * Retrieves authentication endpoint URL and parameters
   * @return string The authentication URL and query string
   */
  function GetAuthURL()
  {
    $params = array(
      "client_id"     => $this->clientID,
      "redirect_uri"  => $this->redirectURI,
      "response_type" => "code",
    );
    if ($this->state != null)
      $params["state"] = $this->state;
    if ($this->scope)
      $params["scope"] = implode(" ",$this->scope);
      
    return self::ENDPOINT_AUTH . "?" . http_build_query($params);
  }

  /**
   * Sends redirect header and stops execution
   */
  function PerformAuthRedirect()
  {
    header( "Location: " . $this->GetAuthURL() );
    exit();
  }

  /**
   * Process the second step of authentication
   * @param string $code The authentication code from the query string
   * @param string $state The "state" parameter
   * @return boolean "true" if successful
   * @throws SceneID3Exception Exception is thrown if the authorization code
   *    is not found
   * @throws SceneID3Exception Exception is thrown if the state mismatches
   * @throws SceneID3AuthException Exception is thrown if authentication fails
   */
  function ProcessAuthResponse( $code = null, $state = null )
  {
    if (!$code)
      $code = $_GET["code"];
    if (!$code)
      throw new SceneID3Exception("Couldn't find authorization code!");

    if (!$state)
      $state = $_GET["state"];
      
    if ($this->state)
    {
      if ( $this->state != $state )
        throw new SceneID3Exception("State mismatch!");
    }

    $authString = "Basic " . base64_encode( $this->clientID . ":" . $this->clientSecret );
    
    $params = array(
      "grant_type"   => "authorization_code",
      "code"         => $code,
      "redirect_uri" => $this->redirectURI,
    );

    $data = $this->Request( self::ENDPOINT_TOKEN, "POST", $params, array("Authorization"=>$authString) );

    $authTokens = json_decode( $data );
    
    if (!$authTokens || !$authTokens->access_token)
      throw new SceneID3AuthException("Authorization failed", 0, null, $data);

    $this->accessToken = $authTokens->access_token;

    return true;
  }
  
  /**
   * Send authenticated request to URL
   * @param string $url The endpoint URL
   * @param string $method GET, POST, PUT, etc.
   * @param string $params Key-value pair of POST data
   * @return string The request response
   * @throws SceneID3Exception Exception is thrown if the class isn't
   *    authenticated yet
   */
  function ResourceRequest( $url = self::ENDPOINT_RESOURCE, $method = "GET", $params = array() )
  {
    if (!$this->accessToken)
      throw new SceneID3Exception("Not authenticated!");
      
    $auth2 = "Bearer ".$this->accessToken;

    $data = $this->Request( $url, $method, $params, array("Authorization"=>$auth2) );

    return $data;
  }
}

class SceneID3 extends SceneID3OAuth
{
  function Test()
  {
    return $this->ResourceRequest( self::ENDPOINT_RESOURCE . "/test/" );
  }
  function Test2()
  {
    return $this->ResourceRequest( self::ENDPOINT_RESOURCE . "/test2/" );
  }
  function Me()
  {
    return $this->ResourceRequest( self::ENDPOINT_RESOURCE . "/me/" );
  }
}

?>