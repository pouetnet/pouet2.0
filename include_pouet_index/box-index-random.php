<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");

class PouetBoxIndexRandom extends PouetBox {
  var $data;
  var $prod;
  function PouetBoxIndexRandom() {
    parent::__construct();
    $this->uniqueID = "pouetbox_random";
    $this->title = "a random prod";
  }

  function LoadFromCachedData($data) {
    $this->data = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->data);
  }

  function LoadFromDB() {
    $s = new BM_Query("prods");
    $s->AddOrder("rand()");
    $s->SetLimit(1);
    list($this->data) = $s->perform();

    $a = array(&$this->data->prod);
    PouetCollectPlatforms($a);
  }

  function RenderContent() {
    if ($this->data)
      $this->data->prod->RenderAsEntry();
  }
  function RenderFooter() {
    echo "</div>\n";
    return $s;
  }
};

//$indexAvailableBoxes[] = "Random";
?>