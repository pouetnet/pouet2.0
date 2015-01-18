<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");

class PouetBoxIndexFeedPoundsOff extends PouetBoxCachable {
  function PouetBoxIndexFeedPoundsOff() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_feedpoundsoff";
    $this->title = "pounds-off !";

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
      "limit" => array("name"=>"number of posts visible","default"=>5,"max"=>10),
    );
  }

  function LoadFromCachedData($data) {
    $this->jsonData = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->jsonData);
  }

  function LoadFromDB() {
    $this->jsonData = json_decode( file_get_contents('http://www.pounds-off.me/?format=json'), true );
  }

  function RenderBody() {
    echo "<ul class='boxlist'>\n";
    for($i=0; $i < min( count($this->jsonData),$this->limit); $i++)
    {
      echo "<li>\n";
      $p = "sucks";
      if($this->jsonData[$i]['status'] == "lost"   && $this->jsonData[$i]['intent'] == "lose weight") $p = "rulez";
      if($this->jsonData[$i]['status'] == "gained" && $this->jsonData[$i]['intent'] == "gain weight") $p = "rulez";
      if($this->jsonData[$i]['status'] == "hold"   && $this->jsonData[$i]['intent'] == "hold weight") $p = "rulez";
      if($this->jsonData[$i]['status'] == "hold"   && $this->jsonData[$i]['intent'] != "hold weight") $p = "isok";
      echo "<img src='".POUET_CONTENT_URL."gfx/".$p.".gif' alt='".$p."' />\n";
      echo "<a href='"._html($this->jsonData[$i]['url'])."'>"._html($this->jsonData[$i]['name'])."</a> "._html(strip_tags($this->jsonData[$i]['message']));
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='http://www.pounds-off.me/'>more at pounds-off</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "FeedPoundsOff";
?>