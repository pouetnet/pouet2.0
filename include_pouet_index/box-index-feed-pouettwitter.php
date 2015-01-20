<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");

class PouetBoxIndexFeedPouetTwitter extends PouetBoxCachable {
  function PouetBoxIndexFeedPouetTwitter() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_feedpouettwitter";
    $this->title = "twitter !";

    //$this->cacheTime = 60*60;

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
    $this->rssData = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->rssData);
  }

  function LoadFromDB() {
    $this->rssData = $this->rss->get('http://www.queryfeed.net/twitter?q=from%3Apouetdotnet');
  }

  function RenderBody() {
    echo "<ul class='boxlist'>\n";
    for($i=0; $i < min( count($this->rssData['items']),$this->limit); $i++)
    {
      echo "<li>\n";
      echo "<a href='".$this->rssData['items'][$i]['link']."'>".strip_tags($this->rssData['items'][$i]['description'])."</a> ";
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