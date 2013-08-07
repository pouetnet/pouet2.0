<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");
include_once("include_pouet/box-index-oneliner-latest.php");
include_once("include_pouet/box-bbs-post.php");
include_once("include_pouet/box-prod-post.php");
include_once("include_pouet/box-bbs-open.php");

$errormessage = "";

////////////////////////////////////////////////////////////

$message = new PouetBoxModalMessage(false,true);
$message->title = "An error has occured:";

$box = NULL;
$thing = "";
$data = "";

if (!$_SESSION["user"]) {

  $message->message = "You got logged out somehow...";

} else {

  switch ($_POST["type"]) {
    case "oneliner":
      {
        $box = new PouetBoxLatestOneliner();
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
    case "comment":
      {
        $box = new PouetBoxProdPost($_POST["which"]);
        $thing = "comment";
        $data = $_POST["comment"];
        $message->returnPage = "prod.php?which=".(int)$_POST["which"];
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

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo $message->Render();

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>
