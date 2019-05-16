<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");

class PouetBoxIndexTopAlltime extends PouetBoxCachable {
  var $data;
  var $prods;
  function __construct() {
    parent::__construct();
    $this->uniqueID = "pouetbox_topalltime";
    $this->title = "all-time top";

    $this->limit = 10;
  }

  function LoadFromCachedData($data) {
    $this->data = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->data);
  }
  
  use PouetFrontPage;
  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
  }
  function GetParameterSettings()
  {
    return array(
      "limit" => array("name"=>"number of prods visible","default"=>10,"min"=>1,"max"=>POUET_CACHE_MAX),
    );
  }

  function LoadFromDB() {
    $s = new BM_Query("prods");
    $s->AddOrder("prods.rank");
    $s->AddWhere("prods.rank!=0");
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();
    PouetCollectPlatforms($this->data);
  }
  function RenderBody() {
    echo "<ul class='boxlist'>\n";
    $n = 0;
    foreach($this->data as $p) {
      echo "<li>\n";
      $p->RenderAsEntry();
      echo "</li>\n";
      if (++$n == $this->limit) break;
    }
    echo "</ul>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='toplist.php'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "TopAlltime";
?>