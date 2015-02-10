<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");

class PouetBoxIndexFeedPouetTwitter extends PouetBoxCachable {
  function PouetBoxIndexFeedPouetTwitter() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_feedpouettwitter";
    $this->title = "twitter @pouetdotnet";

    $this->cacheTime = 60*60;

    $this->rss = new lastRSS(array(
      "cacheTime" => 5 * 60, // in seconds
      "dateFormat" => "Y-m-d",
      "stripHtml" => false,
    ));
    $this->rss->setItemTags(array(
      "link",
      "description",
    ));
    
    $this->limit = 5;
  }

  use PouetFrontPage;
  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
  }
  function GetParameterSettings()
  {
    return array(
      "limit" => array("name"=>"number of tweets visible","default"=>5,"max"=>10),
    );
  }

  function LoadFromCachedData($data) {
    $this->jsonData = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->jsonData);
  }

  function Sideload( $url, $data = array(), $headers = array(), $method = "GET" )
  {
    if ($method == "GET")
      $url .= (strstr($url,"?") === false ? "?" : "&") . http_build_query($data);
    return file_get_contents( $url, false, stream_context_create( array(
      'http'=>array(
        'method'=>$method,
        'header'=>implode("\r\n",$headers),
        'content'=>($method == "GET" ? null : http_build_query($data)),
      ),
      'ssl' => array(
        'verify_peer' => false,
      ),
    ) ) );
  }
  function LoadFromDB() 
  {
    if (!defined("TWITTER_CONSUMER_KEY")) return;
    
    $auth = "Basic " . base64_encode( TWITTER_CONSUMER_KEY . ":" . TWITTER_CONSUMER_SECRET );
    
    $authTokens = json_decode( $this->Sideload( "https://api.twitter.com/oauth2/token", array("grant_type"=>"client_credentials"), array("Authorization: ".$auth), "POST" ) );
    if (!$authTokens || !$authTokens->access_token)
      return;
    $auth2 = "Bearer ".$authTokens->access_token;
    
    // doc @ https://dev.twitter.com/rest/reference/get/statuses/user_timeline
  
    $statuses = array();
    
    $data = json_decode( $this->Sideload( "https://api.twitter.com/1.1/statuses/user_timeline.json", array("screen_name"=>"pouetdotnet","count"=>10), array("Authorization: ".$auth2) ) );
    if (!$data || !is_array($data))
      return;
    
    $this->jsonData = $data;
  }

  function RenderBody() {
    echo "<ul class='boxlist boxlisttable'>\n";
    for($i=0; $i < min( count($this->jsonData),$this->limit); $i++)
    {
      echo "<li>\n";
      $tweet = $this->jsonData[$i];
      if ($tweet->retweeted_status)
        $tweet = $tweet->retweeted_status;
        
      echo "<span><img src='"._html($tweet->user->profile_image_url_https)."' width='16'></span>";
      echo "<span><a href='https://twitter.com/pouetdotnet/status/"._html($tweet->id_str)."'>"._html(strip_tags($tweet->text))."</a></span>";
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='http://twitter.com/pouetdotnet'>more at @pouetdotnet !</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "FeedPouetTwitter";
?>
