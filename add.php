<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet_index/box-index-oneliner-latest.php");
require_once("include_pouet/box-bbs-post.php");
require_once("include_pouet/box-bbs-open.php");

$errormessage = "";

////////////////////////////////////////////////////////////

$message = new PouetBoxModalMessage(false,true);
$message->title = "An error has occured:";

$box = NULL;
$thing = "";
$data = "";

if (!$currentUser) {

  $message->message = "You got logged out somehow...";

} else {
  if (isset($array['type'])) {

    switch ($_POST["type"]) {
      case "oneliner":
        {
          $box = new PouetBoxIndexLatestOneliner();
          $thing = "oneline";
          $data = $_POST["message"];
          $message->returnPage = "index.php";
        } break;
      case "post":
        {
          $box = new PouetBoxBBSPost($_POST["which"]);
          $thing = "BBS post";
          $data = $_POST["message"];
          $message->returnPage = "topic.php?which=".(int)$_POST["which"];
        } break;
      case "bbs":
        {
          $box = new PouetBoxBBSOpen();
          $thing = "bbs";
          $data = $_POST["message"];
          $message->returnPage = "index.php";
        } break;
      default:
        {
          $message->message = "not implemented!";
        } break;
    }
  }
}
if ($box) {
  $csrf = new CSRFProtect();
  if (!$csrf->ValidateToken())
  {
    $message->classes[] = "errorbox";
    $message->message = "who are you and where did you come from ?";
  }
  else
  {
    $errormessage = $box->ParsePostMessage($_POST);
    if (!$errormessage) {
      $message->title = "You've successfully added the following ".$thing.":";
      $message->message = $data;
      if ($box instanceof PouetBoxCachable)
        $box->ForceCacheUpdate();
    } else {
      $message->classes[] = "errorbox";
      $message->message = is_array($errormessage) ? implode("<br/>",$errormessage) : $errormessage;
    }
  }
} else {
  $message->message = "not implemented!";
}

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo $message->Render();

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
