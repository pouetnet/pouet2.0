<?php
class PouetBoxIndexCDC extends PouetBoxCachable 
{
  var $data;
  var $prod;
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_cdc";
    $this->title = "coup de coeur";
  }

  function LoadFromCachedData($data) 
  {
    $this->data = unserialize($data);
  }

  function GetCacheableData() 
  {
    return serialize($this->data);
  }

  function LoadFromDB() 
  {
    $s = new BM_Query();
    $s->AddTable("cdc");
    $s->AddField("cdc.addedDate");
    $s->attach(array("cdc"=>"which"),array("prods as prod"=>"id"));
    $s->AddOrder("cdc.addedDate desc");
    $s->SetLimit(1);
    list($this->data) = $s->perform();

    $a = array(&$this->data->prod);
    PouetCollectPlatforms($a);
  }

  function RenderContent() 
  {
    //return $this->prod->RenderLink() . " $ " . $this->prod->RenderGroupsShort();
    if ($this->data && $this->data->prod)
      $this->data->prod->RenderAsEntry();
  }
  function RenderFooter() 
  {
    global $currentUser;
    if ($currentUser && $currentUser->IsModerator())
    {
      $dif = time() - strtotime($this->data->addedDate);
      if ($dif > 60 * 60 * 24 * 30)
      {
        echo "<div class='content notifications'>this current cdc is ".secToReadable($dif,true)." old</div>\n";
      }
    }
    echo "  <div class='foot'><a href='awards.php'>awards</a> :: <a href='cdc.php'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "CDC";
?>