<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");
include_once("include_pouet/box-index-oneliner-latest.php");
include_once("include_pouet/box-bbs-post.php");
include_once("include_pouet/box-prod-post.php");

$errormessage = "";

////////////////////////////////////////////////////////////

$message = new PouetBoxModalMessage(false,true);
$message->classes[] = "errorbox";
$message->title = "An error has occured:";
$message->message = $_GET["e"];

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo $message->Render();

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>