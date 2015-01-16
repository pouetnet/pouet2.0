<?
class PouetBoxIndexWatchlist extends PouetBox {
  var $data;
  var $prods;
  function PouetBoxIndexWatchlist() {
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
      "limit"      => array("name"=>"number of prods visible","default"=>5,"max"=>POUET_CACHE_MAX),
    );
  }
  
  function IsVisibleLoggedOut() 
  {
    return false;
  }


  function LoadFromDB() {
    global $currentUser;
    if (!$currentUser) return;
    
    $ids = SQLLib::SelectRows(sprintf_esc("select prodID from watchlist where userID = %d",$currentUser->id));
    if (!count($ids)) return;
    
    $i = array();
    foreach($ids as $v) $i[] = $v->prodID;
    
    $s = new BM_Query();
    $s->AddTable(sprintf_esc("(select * from comments where comments.which in (%s) order by comments.addedDate desc) as c ",implode(",",$i)));
    $s->attach(array("c"=>"which"),array("prods as prod"=>"id"));
    $s->attach(array("c"=>"who"),array("users as user"=>"id"));
    $s->AddGroup("c.which");
    $s->AddOrder("c.addedDate desc");
    $s->AddField("c.id as commentID");
    $s->SetLimit((int)$this->limit);
    
    $this->data = $s->perform();
  }

  function RenderBody() {
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
    }
    else
    {
      echo "<div class='content'>your watchlist is currently empty</div>";
    }
  }
  function RenderFooter() {
    //echo "  <div class='foot'><a href='prodlist.php?order=added'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

?>
