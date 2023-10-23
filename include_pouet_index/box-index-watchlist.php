<?php
class PouetBoxIndexWatchlist extends PouetBox
{
  public $data;
  public $prods;
  public $limit;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_watchlist";
    $this->title = "your watchlist";

    $this->limit = 5;
  }

  function GetData()
  {
    return $this->data;
  }

  use PouetFrontPage;
  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
  }
  function GetParameterSettings()
  {
    return array(
      "limit"      => array("name"=>"number of prods visible","default"=>5,"min"=>1,"max"=>POUET_CACHE_MAX),
    );
  }

  function IsVisibleLoggedOut()
  {
    return false;
  }


  function LoadFromDB()
  {
    global $currentUser;
    if (!$currentUser) return;

    $ids = SQLLib::SelectRows(sprintf_esc("select prodID from watchlist where userID = %d",$currentUser->id));
    if (!count($ids)) return;

    $i = array();
    foreach($ids as $v) $i[] = $v->prodID;

    $s = new BM_Query();
    //$s->AddTable(sprintf_esc("(select * from comments where comments.which in (%s) order by comments.addedDate desc) as c ",implode(",",$i)));
    $s->AddTable(sprintf_esc("(select *, max(comments.addedDate) as maxDate from comments where comments.which in (%s) group by comments.which) as c ",implode(",",$i)));
    $s->AddJoin("left","comments","c.maxDate = comments.addedDate and c.which = comments.which");
    $s->attach(array("comments"=>"which"),array("prods as prod"=>"id"));
    $s->attach(array("comments"=>"who"),array("users as user"=>"id"));
    //$s->AddGroup("c.which");
    $s->AddOrder("comments.addedDate desc");
    $s->AddField("comments.id as commentID");
    $s->SetLimit((int)$this->limit);
    $this->data = $s->perform();
  }

  function RenderBody()
  {
    global $currentUser;
    if (!$currentUser) return;

    if ($this->data)
    {
      echo "<ul class='boxlist boxlisttable'>\n";
      $n = 0;
      foreach($this->data as $p)
      {
        echo "<li>\n";
        echo "<span class='rowprod'>\n";
        echo $p->prod->RenderAsEntry();
        echo "</span>\n";
        echo "<span class='rowuser'>\n";
        echo $p->user->PrintLinkedAvatar();
        echo "</span>\n";
        echo "</li>\n";
        if (++$n == $this->limit) break;
      }
      echo "</ul>\n";
      echo "  <div class='foot'><a href='user_watchlist.php'>more</a>...</div>\n";
    }
    else
    {
      echo "<div class='content'>your watchlist is currently empty</div>";
    }
  }
  function RenderFooter()
  {
    //echo "  <div class='foot'><a href='prodlist.php?order=added'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "Watchlist";
?>