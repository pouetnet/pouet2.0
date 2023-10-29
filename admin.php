<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("admin.functions.php");

if (!$currentUser || !$currentUser->IsGloperator())
{
  redirect("index.php");
  exit();
}

function PouetAdmin_recacheFrontPagePartial_Form()
{
  echo "<ul>";
  foreach(glob("cache/*") as $v) { echo "<li><input type='checkbox' name='deleteCache["._html(basename($v))."]' id='"._html($v)."'/> <label for='"._html($v)."'>"._html($v)."</label></li>\n"; }
  echo "</ul>";
}

class PouetBoxAdmin extends PouetBox {
  function __construct() {
    parent::__construct();
    $this->uniqueID = "pouetbox_admin";
    $this->title = "i'm gonna wreck it !";
  }

  function Render()
  {
    global $currentUser;
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    echo "<h2>i'm gonna wreck it !</h2>\n";
    echo "<ul class='boxlist'>\n";
    $actions = array(
      "recacheFrontPagePartial" => "flush front page cache",
      "recacheFrontPage" => "flush entire front page cache",
      "recacheTopDemos" => "recalculate top demo list",
      "recacheBBS" => "recalculate latest 20 BBS caches",
    );
    foreach($actions as $k=>$v)
    {
      echo "  <li>";
      echo "<form method='post'>";

      $csrf = new CSRFProtect();
      $csrf->PrintToken();

      echo _html($v).": ";
      $func = "PouetAdmin_".$k."_Form";
      if (function_exists($func)) $func();
      
      echo "<input name='".$k."' type='submit' value='submit'/>";
      echo "</form>";
      echo "</li>\n";
    }
    echo "  <li><a href='admin_modification_requests.php'>process modification requests</a></li>";
    if ($currentUser->IsModerator())
    {
      echo "  <li><a href='admin_compotypes_edit.php'>edit compo types</a></li>";
      echo "  <li><a href='admin_faq.php'>edit faq items</a></li>";
      echo "  <li><a href='admin_cdc.php'>edit cdcs</a></li>";
    }
    echo "</ul>\n";
    echo "</div>\n";
  }
};

$TITLE = "admin";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$content = "";
if ($_POST)
{
  $csrf = new CSRFProtect();
  if ($csrf->ValidateToken())
  {
    foreach($_POST as $k=>$v)
    {
      $func = "pouetAdmin_".$k;
      if (function_exists($func))
        $content .= $func();
    }
  }
}

if (!get_login_id())
{
  require_once("include_pouet/box-login.php");
  $box = new PouetBoxLogin();
  $box->Render();
}

if ($content)
{
  $msg = new PouetBoxModalMessage( true );
  $msg->classes[] = "successbox";
  $msg->title = "Success!";
  $msg->message = $content;
  $msg->Render();
}

$box = new PouetBoxAdmin();
$box->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
