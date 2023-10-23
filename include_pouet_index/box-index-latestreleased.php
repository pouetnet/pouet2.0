<?php
class PouetBoxIndexLatestReleased extends PouetBoxCachable
{
  public $data;
  public $prods;
  public $limit;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestreleased";
    $this->title = "latest released prods";

    $this->limit = 5;
  }

  function LoadFromCachedData($data)
  {
    $this->data = unserialize($data);
  }

  function GetCacheableData()
  {
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
      "limit" => array("name"=>"number of prods visible","default"=>5,"min"=>1,"max"=>POUET_CACHE_MAX),
    );
  }

  function LoadFromDB()
  {
    $s = new BM_Query("prods");
    $s->AddOrder("prods.releaseDate DESC,prods.addedDate DESC");
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();
    PouetCollectPlatforms($this->data);
  }

  function RenderBody()
  {
    echo "<ul class='boxlist boxlisttable'>\n";
    $n = 0;
    foreach($this->data as $p) {
      echo "<li>\n";
      echo "<span class='rowprod'>\n";
      echo $p->RenderAsEntry();
      echo "</span>\n";
      if (get_setting("indexwhoaddedprods"))
      {
        echo "<span class='rowuser'>\n";
        echo $p->addedUser->PrintLinkedAvatar();
        echo "</span>\n";
      }
      echo "</li>\n";
      if (++$n == $this->limit) break;
    }
    echo "</ul>\n";
  }
  function RenderFooter()
  {
    echo "  <div class='foot'><a href='prodlist.php?order=release'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "LatestReleased";
?>