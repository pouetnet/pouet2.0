<?
require_once("bootstrap.inc.php");
require_once("include_pouet/pouet-user.php");

//$csrf = new CSRFProtect();
if ($_GET["error"])
  redirect("error.php?e=".rawurlencode( $_GET["error_description"] ));

if (!$_GET["code"])
{
  $sceneID->PerformAuthRedirect();
  exit();
}

$rv = null;
$err = "";
try
{
  $sceneID->ProcessAuthResponse();

  unset($_SESSION["user"]);
  unset($_SESSION["settings"]);

  session_regenerate_id(true);

  $user = $sceneID->Me();

  if (!$user["success"] || !$user["user"]["id"])
  {
		redirect("error.php?e=".rawurlencode("User not found."));
  }
  
  $user = PouetUser::Spawn( (int)$user["user"]["id"] );
  if (!$user || !$user->id)
  {
    $entry = glob(POUET_CONTENT_LOCAL."avatars/*.gif");
    $r = $entry[array_rand($entry)];
    $a = basename($r);

    $user = new PouetUser();
    $user->id = (int)$user["user"]["id"];
    $user->nickname = $user["user"]["display_name"];
    $user->avatar = $a;

    $user->Create();

    $user = PouetUser::Spawn( $user->id );
  }

  if ( $user->IsBanned() )
  {
		redirect("error.php?e=".rawurlencode("We dun like yer type 'round these parts."));
  }

  $_SESSION["user"] = $user;
  $_SESSION["settings"] = SQLLib::SelectRow(sprintf_esc("select * from usersettings where id=%d",$_SESSION["user"]->id));

  redirect( basename($_POST["return"]?$_POST["return"]:"index.php") );
  
}
catch(SceneID3Exception $e) 
{
	redirect("error.php?e=".rawurlencode( $e->GetMessage() ));
}

?>
