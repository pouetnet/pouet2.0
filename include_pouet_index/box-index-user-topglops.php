<?php
class PouetBoxIndexTopGlops extends PouetBoxCachable
{
  public $data;
  public $prods;
  public $limit;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_topglops";
    $this->title = "top of the glöps";

    $this->limit = 10;
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
      "limit" => array("name"=>"number of users visible","default"=>10,"min"=>1,"max"=>POUET_CACHE_MAX),
    );
  }

  function LoadFromDB()
  {
    $s = new BM_Query("users");
    $s->AddOrder("users.glops desc");
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();
  }

  function RenderBody()
  {
    echo "<ul class='boxlist'>\n";
    $n = 0;
    foreach($this->data as $p) {
      echo "<li>\n";
      echo $p->PrintLinkedAvatar()." ";
      echo "<span class='prod'>".$p->PrintLinkedName()."</span>\n";
      echo "<span class='group'>:: ".$p->glops." glöps</span>\n";
      echo "</li>\n";
      if (++$n == $this->limit) break;
    }
    echo "</ul>\n";
  }
  function RenderFooter()
  {
    echo "  <div class='foot'><a href='userlist.php'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "TopGlops";
?>