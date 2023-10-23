<?php
class PouetBoxIndexLatestComments extends PouetBoxCachable
{
  public $data;
  public $prods;
  public $limit;
  public $showUser;
  public $showVote;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestcomments";
    $this->title = "latest comments added";

    $this->limit = 5;
    $this->showUser = true;
    $this->showVote = false;
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
    if (isset($data["showUser"])) $this->showUser = $data["showUser"];
    if (isset($data["showVote"])) $this->showVote = $data["showVote"];
  }
  function GetParameterSettings()
  {
    return array(
      "limit" => array("name"=>"number of comments visible","default"=>5,"min"=>1,"max"=>POUET_CACHE_MAX),
      "showUser" => array("name"=>"show user avatar","default"=>true,"type"=>"checkbox"),
      "showVote" => array("name"=>"show vote with comment","default"=>false,"type"=>"checkbox"),
    );
  }

  function LoadFromDB()
  {
    $s = new BM_Query();
    $s->AddTable("(select * from comments order by comments.addedDate desc limit 25) as c");
    $s->attach(array("c"=>"which"),array("prods as prod"=>"id"));
    $s->attach(array("c"=>"who"),array("users as user"=>"id"));
    $s->AddOrder("c.addedDate desc");
    $s->AddField("c.id as commentID");
    $s->AddField("c.rating as rating");
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();

    $a = array();
    foreach($this->data as $p) $a[] = &$p->prod;
    PouetCollectPlatforms($a);
  }

  function RenderBody()
  {
    echo "<ul class='boxlist boxlisttable'>\n";
    $n = 0;
    foreach($this->data as $d)
    {
      echo "<li>\n";
      echo "<span class='rowprod'>\n";
      echo $d->prod->RenderAsEntry();
      echo "</span>\n";
      if ($this->showUser)
      {
        echo "<span class='rowuser'>\n";
        echo $d->user->PrintLinkedAvatar();
        echo "</span>\n";
      }
      if ($this->showVote)
      {
        $p = "isok";
        if ($d->rating < 0) $p = "sucks";
        if ($d->rating > 0) $p = "rulez";
        echo "<span class='rowvote ".$p."' title='".$p."'>".$p."</span>\n";
      }
      echo "</li>\n";
      if (++$n == $this->limit) break;
    }
    echo "</ul>\n";
  }
  function RenderFooter()
  {
    echo "  <div class='foot'><a href='comments.php'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "LatestComments";
?>