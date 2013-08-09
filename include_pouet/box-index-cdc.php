<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");

class PouetBoxCDC extends PouetBoxCachable {
  var $data;
  var $prod;
  function PouetBoxCDC() {
    parent::__construct();
    $this->uniqueID = "pouetbox_cdc";
    $this->title = "coup de coeur";
  }

  function LoadFromCachedData($data) {
    $this->data = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->data);
  }

  function LoadFromDB() {
    $s = new BM_Query();
    $s->AddTable("cdc");
    $s->attach(array("cdc"=>"which"),array("prods as prod"=>"id"));
    $s->AddOrder("cdc.quand desc");
    $s->SetLimit(1);
    list($this->data) = $s->perform();

    $a = array(&$this->data->prod);
    PouetCollectPlatforms($a);
  }

  function RenderContent() {
    //return $this->prod->RenderLink() . " $ " . $this->prod->RenderGroupsShort();
    if ($this->data)
      $this->data->prod->RenderAsEntry();
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='sceneorg.php'>scene.org awards</a> :: <a href='cdc.php'>more</a>...</div>\n";
    echo "</div>\n";
    return $s;
  }
};

?>
