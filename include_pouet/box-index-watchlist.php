<?
class PouetBoxWatchlist extends PouetBox {
  var $data;
  var $prods;
  function PouetBoxWatchlist() {
    parent::__construct();
    $this->uniqueID = "pouetbox_watchlist";
    $this->title = "your watchlist";

    $this->limit = 10;
  }

  function GetData()
  {
    return $this->data;
  }

  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
  }

  function LoadFromDB() {
    global $currentUser;
    if (!$currentUser) return;
    
    $s = new BM_Query();
    $s->AddTable(sprintf_esc("(select * from comments where comments.which in (select prodID from watchlist where userID = %d) order by comments.quand desc limit 25) as c ",$currentUser->id));
    $s->attach(array("c"=>"which"),array("prods as prod"=>"id"));
    $s->attach(array("c"=>"who"),array("users as user"=>"id"));
    $s->AddGroup("c.which");
    $s->AddOrder("c.quand desc");
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
