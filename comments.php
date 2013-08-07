<?
include_once("bootstrap.inc.php");

class PouetBoxLatestComments extends PouetBox {
  function PouetBoxLatestComments() {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestcomments";
    $this->title = "latest comments in the last 24 hours";
  }

  function LoadFromDB()
  {
    $s = new BM_Query("comments");
    $s->AddField("comments.rating");
    $s->AddField("comments.quand");
    $s->attach(array("comments"=>"which"),array("prods as prod"=>"id"));        
    $s->attach(array("comments"=>"who"),array("users as user"=>"id"));        
    $s->AddOrder("comments.quand DESC");
    $s->AddWhere(sprintf_esc("(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(comments.quand))<=(3600*%d)",get_setting("commentshours")));
    $this->comments = $s->perform();
    
    $a = array();
    foreach($this->comments as $v) $a[] = &$v->prod;
    PouetCollectPlatforms($a);
  }
  
  function RenderBody() 
  {
    echo "\n\n";
    echo "<table class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th><img src='".POUET_CONTENT_URL."gfx/rulez.gif'/></th>\n";
    echo "  <th>name</th>\n";
    echo "  <th>group</th>\n";
    echo "  <th>platform</th>\n";
    echo "  <th>time</th>\n";
    echo "  <th>user</th>\n";
    echo "</tr>\n";

    foreach ($this->comments as $row)
    {
      $p = $row->prod;
      echo "<tr>\n";


      $rating = $row->rating>0 ? "rulez" : ($row->rating<0 ? "sucks" : "isok");
      
      echo "<td>\n";
      echo "<img src='".POUET_CONTENT_URL."gfx/".$rating.".gif'/>";
      echo "</td>\n";

      echo "<td>\n";
//      echo "<img src='".POUET_CONTENT_URL."gfx/sceneorg/".$row->type.".gif' alt='".$row->type."'/>&nbsp;";
      //echo $p->RenderTypeIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderPlatformIcons();
      echo "</td>\n";
      
      echo "<td class='date'>\n";
      echo dateDiffReadable( time(), $row->quand)." ago";
      echo "</td>\n";
      
      echo "<td>\n";
      echo $row->user->PrintLinkedAvatar()." ";
      echo $row->user->PrintLinkedName()." ";
      echo "</td>\n";
      
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};

$TITLE = "latest comments";

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box = new PouetBoxLatestComments();
$box->Load();
$box->Render();

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");

?>
