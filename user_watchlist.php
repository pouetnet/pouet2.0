<?
require_once("bootstrap.inc.php");

class PouetBoxUserWatchlist extends PouetBox {
  function PouetBoxUserWatchlist() {
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
  
    $s = new BM_Query("prods");
    $s->AddWhere( sprintf_esc("prods.id in (%s)",implode(",",$i) ) );
    $this->prods = $s->perform();

    PouetCollectPlatforms($this->prods);
  }

  function RenderBody()
  {
    echo "\n\n";
    echo "<table class='boxtable'>\n";
    echo "<tr>\n";
    //echo "  <th><img src='".POUET_CONTENT_URL."gfx/rulez.gif'/></th>\n";
    echo "  <th>name</th>\n";
    echo "  <th>group</th>\n";
    echo "  <th>platform</th>\n";
    //echo "  <th>time</th>\n";
    //echo "  <th>user</th>\n";
    echo "</tr>\n";

    foreach ($this->prods as $p)
    {
      echo "<tr>\n";

      echo "<td>\n";
//      echo "<img src='".POUET_CONTENT_URL."gfx/sceneorg/".$row->type.".gif' alt='".$row->type."'/>&nbsp;";
      echo $p->RenderTypeIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderPlatformIcons();
      echo "</td>\n";

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
