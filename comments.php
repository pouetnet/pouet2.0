<?php
require_once("bootstrap.inc.php");

class PouetBoxLatestComments extends PouetBox
{
  public $comments;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestcomments";
    $this->title = sprintf("latest comments in the last %d hours",get_setting("commentshours"));
  }

  function LoadFromDB()
  {
    $s = new BM_Query();
    $s->AddTable("comments");
    $s->AddField("comments.rating");
    $s->AddField("comments.addedDate");
    $s->attach(array("comments"=>"which"),array("prods as prod"=>"id"));
    $s->attach(array("comments"=>"who"),array("users as user"=>"id"));
    $s->AddOrder("comments.addedDate DESC");
    $s->AddWhere(sprintf_esc("(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(comments.addedDate))<=(3600*%d)",get_setting("commentshours")));
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
    echo "  <th><span class='rulez'>vote</span></th>\n";
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
      echo "<span class='icon ".$rating."' title='".$rating."'>".$rating."</span>";
      echo "</td>\n";

      echo "<td>\n";
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderPlatformIcons();
      echo "</td>\n";

      echo "<td class='date'>\n";
      echo dateDiffReadable( time(), $row->addedDate)." ago";
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

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box = new PouetBoxLatestComments();
$box->Load();
$box->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
