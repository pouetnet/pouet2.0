<?php
require_once("bootstrap.inc.php");

class PouetBoxSubmit extends PouetBox {
  function __construct() {
    parent::__construct();
    $this->uniqueID = "pouetbox_submit";
    $this->title = "What do you want to do?";
  }

  function RenderBody()
  {
    //echo "\n\n";
    //echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    if (get_login_id())
    {
      echo "<ul class='boxlist'>\n";
      echo "  <li><a href='submit_prod.php'>submit a prod</a></li>\n";
      echo "  <li><a href='submit_group.php'>submit a group</a></li>\n";
      echo "  <li><a href='submit_party.php'>submit a party</a></li>\n";
      echo "  <li><a href='submit_board.php'>submit a bulletin board</a></li>\n";
      echo "  <li><a href='submit_avatar.php'>upload an avatar</a></li>\n";
      echo "  <li><a href='submit_logo.php'>upload a logo</a></li>\n";
      echo "  <li><a href='submit_list.php'>create a list</a></li>\n";
      echo "  <li><a href='logo_vote.php'>vote on logos</a></li>\n";
      echo "</ul>\n";
    }
    echo "<h2>free 4 all stuffz!</h2>\n";
    echo "<ul class='boxlist'>\n";
    echo "  <li><a href='http://www.bitfellas.org/submitnews.php'>submit news via bitfellas</a></li>\n";
    echo "</ul>\n";
    //echo "</div>\n";
  }
};

$TITLE = "submit";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (!get_login_id())
{
  require_once("include_pouet/box-login.php");
  $box = new PouetBoxLogin();
  $box->Render();
}

$box = new PouetBoxSubmit();
$box->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
