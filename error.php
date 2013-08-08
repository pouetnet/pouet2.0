<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-index-oneliner-latest.php");
require_once("include_pouet/box-bbs-post.php");
require_once("include_pouet/box-prod-post.php");

$errormessage = "";

////////////////////////////////////////////////////////////

$message = new PouetBoxModalMessage(false,true);
$message->classes[] = "errorbox";
$message->title = "An error has occured:";
$message->message = $_GET["e"];

require_once("include_pouet/header.php");
require_once("include_pouet/menu.inc.php");

echo $message->Render();

require_once("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
