<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");

class PouetBoxTopMonth extends PouetBoxCachable {
  var $data;
  var $prods;
  function PouetBoxTopMonth() {
    parent::__construct();
    $this->uniqueID = "pouetbox_topmonth";
    $this->title = "top of the month";

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
    $s->AddOrder("(prods.views/((sysdate()-prods.quand)/100000)+prods.views)*prods.voteavg*prods.voteup DESC");
    $s->AddWhere("prods.quand BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()");
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
    echo "  <div class='foot'><a href='toplist.php?days=30'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

?>
