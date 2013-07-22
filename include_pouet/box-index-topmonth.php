<?
include_once("include_generic/sqllib.inc.php");
include_once("include_pouet/pouet-box.php");
include_once("include_pouet/pouet-prod.php");

class PouetBoxTopMonth extends PouetBoxCachable {
  var $data;
  var $prods;
  function PouetBoxTopMonth() {
    parent::__construct();
    $this->uniqueID = "pouetbox_topmonth";
    $this->title = "top of the month";
  }

  function LoadFromCachedData($data) {
    $this->data = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->data);
  }

  function LoadFromDB() {
    $s = new BM_Query("prods");
    $s->AddOrder("(prods.views/((sysdate()-prods.quand)/100000)+prods.views)*prods.voteavg*prods.voteup DESC");
    $s->AddWhere("prods.quand > DATE_SUB(sysdate(),INTERVAL '30' DAY) AND prods.quand < DATE_SUB(sysdate(),INTERVAL '0' DAY)");
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
      if (++$n == get_setting("indextopprods")) break;
    }
    echo "</ul>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='prodlist.php?order=added'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

?>