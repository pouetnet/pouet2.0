<?
include_once("include_generic/sqllib.inc.php");
include_once("include_pouet/pouet-box.php");
include_once("include_pouet/pouet-prod.php");

class PouetBoxTopAlltime extends PouetBoxCachable {
  var $data;
  var $prods;
  function PouetBoxTopAlltime() {
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
  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
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
    echo "  <div class='foot'><a href='prodlist.php?order=added'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

?>