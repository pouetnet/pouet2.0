<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

$errormessage = "";

////////////////////////////////////////////////////////////

$message = new PouetBoxModalMessage(false,true);
$message->classes[] = "errorbox";
$message->title = "An error has occured:";
$message->message = $_GET["e"];

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo $message->Render();

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
