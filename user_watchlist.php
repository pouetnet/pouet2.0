<?php
require_once("bootstrap.inc.php");

class PouetBoxUserWatchlist extends PouetBox 
{
  public $prods;
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_userwatchlist";
    $this->title = "your watchlist";
  }

  function LoadFromDB()
  {
    global $currentUser;
    $ids = SQLLib::SelectRows(sprintf_esc("select prodID from watchlist where userID = %d",$currentUser->id));
    if (!count($ids)) return;
    
    $i = array();
    foreach($ids as $v) $i[] = $v->prodID;
  
    $sub = new SQLSelect();
    $sub->AddField("max(comments.addedDate) as maxDate");
    $sub->AddField("comments.which");
    $sub->AddTable("comments");
    $sub->AddJoin("left","prods","prods.id = comments.which");
    $sub->AddGroup("comments.which");
    $sub->AddWhere( sprintf_esc("prods.id in (%s)",implode(",",$i) ) );
  
    $s = new BM_Query("prods");
    $s->AddField("cmts.addedDate as lastcomment");
    $s->AddField("cmts.rating as lastcommentrating");
    $s->AddWhere( sprintf_esc("prods.id in (%s)",implode(",",$i) ) );
    $s->AddJoin("left","(select comments.addedDate,comments.who,comments.which,comments.rating from (".$sub->GetQuery().") as dummy left join comments on dummy.maxDate = comments.addedDate and dummy.which = comments.which) as cmts","cmts.which=prods.id");
    $s->attach(array("cmts"=>"who"),array("users as user"=>"id"));
    $s->AddOrder("cmts.addedDate DESC");
    $this->prods = $s->perform();

    PouetCollectPlatforms($this->prods);
  }

  function RenderBody()
  {
    echo "\n\n";
    echo "<table class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th>name</th>\n";
    echo "  <th>group</th>\n";
    echo "  <th>platform</th>\n";
    echo "  <th>last comment</th>\n";
    echo "</tr>\n";

    foreach ($this->prods as $p)
    {
      echo "<tr>\n";

      echo "<td>\n";
      echo $p->RenderTypeIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderPlatformIcons();
      echo "</td>\n";

      if ($p->user)
      {
        $rating = "isok";
        if ($p->lastcommentrating < 0) $rating = "sucks";
        if ($p->lastcommentrating > 0) $rating = "rulez";
        echo "<td>";
        echo "<span class='vote ".$rating."'>".$rating."</span> ";
        echo $p->lastcomment." ".$p->user->PrintLinkedAvatar()."</td>\n";
      }
      else
        echo "<td>&nbsp;</td>";

      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};

$TITLE = "your watchlist";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

if ($currentUser)
{
  echo "<div id='content'>\n";
  
  $box = new PouetBoxUserWatchlist();
  $box->Load();
  $box->Render();
  
  echo "</div>\n";
}

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
