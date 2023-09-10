<?php
/*
class PouetBoxIndexFeedPouetTwitter extends PouetBoxCachable {
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_feedpouettwitter";
    $this->title = "twitter @pouetdotnet";

    $this->cacheTime = 60*60;

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
      "limit" => array("name"=>"number of tweets visible","default"=>5,"min"=>1,"max"=>10),
    );
  }

  function LoadFromCachedData($data) {
    $this->jsonData = unserialize($data);
  }

  function GetCacheableData() {
    return $this->jsonData ? serialize($this->jsonData) : false;
  }

  function LoadFromDB() 
  {
    if (!defined("TWITTER_CONSUMER_KEY")) return;
    
    $auth = "Basic " . base64_encode( TWITTER_CONSUMER_KEY . ":" . TWITTER_CONSUMER_SECRET );
    
    $sideload = new Sideload();
    
    $response = $sideload->Request( "https://api.twitter.com/oauth2/token", "POST", array("grant_type"=>"client_credentials"), array("Authorization" => $auth) );
    $authTokens = json_decode( $response );
    if (!$authTokens || !$authTokens->access_token)
    {
      LOG::Warning("Twitter login failed: ".$response);
      return;
    }
    $auth2 = "Bearer ".$authTokens->access_token;
    
    // doc @ https://dev.twitter.com/rest/reference/get/statuses/user_timeline
  
    $statuses = array();
    
    $response = $sideload->Request( "https://api.twitter.com/1.1/statuses/user_timeline.json", "GET", array("screen_name"=>"pouetdotnet","count"=>10), array("Authorization" => $auth2) );
    $data = json_decode( $response );
    if (!$data || !is_array($data))
    {
      LOG::Warning("Twitter query failed: ".$response);
      return;
    }
    
    $this->jsonData = $data;
  }

  function RenderBody() {
    if (!$this->jsonData)
    {
      return;
    }
    echo "<ul class='boxlist boxlisttable'>\n";
    for($i=0; $i < min( count($this->jsonData),$this->limit); $i++)
    {
      echo "<li>\n";
      $tweet = $this->jsonData[$i];
      if ($tweet->retweeted_status)
        $tweet = $tweet->retweeted_status;
        
      echo "<span><img src='"._html($tweet->user->profile_image_url_https)."' width='16'></span>";
      echo "<span><a href='https://twitter.com/"._html($tweet->user->screen_name)."/status/"._html($tweet->id_str)."'>".strip_tags($tweet->text)."</a></span>";
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='https://twitter.com/pouetdotnet'>more at @pouetdotnet !</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "FeedPouetTwitter";
*/
?>
