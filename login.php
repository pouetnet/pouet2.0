<?
require_once("bootstrap.inc.php");
require_once("include_pouet/pouet-user.php");

//$csrf = new CSRFProtect();
if ($_GET["error"])
  redirect("error.php?e=".rawurlencode( $_GET["error_description"] ));

if (!$_GET["code"])
{
  $_SESSION["__return"] = $_GET["return"];
  $sceneID->PerformAuthRedirect();
  exit();
}

$rv = null;
$err = "";
try
{
  $returnURL = $_SESSION["__return"];
  unset($_SESSION["__return"]);
  
  $sceneID->ProcessAuthResponse();

  unset($_SESSION["user"]);

  session_regenerate_id(true);

  $SceneIDuser = $sceneID->Me();

  if (!$SceneIDuser["success"] || !$SceneIDuser["user"]["id"])
  {
		redirect("error.php?e=".rawurlencode("User not found."));
  }
  
  $user = PouetUser::Spawn( (int)$SceneIDuser["user"]["id"] );
  if (!$user || !$user->id)
  {
    $entry = glob(POUET_CONTENT_LOCAL."avatars/*.gif");
    $r = $entry[array_rand($entry)];
    $a = basename($r);

    $user = new PouetUser();
    $user->id = (int)$SceneIDuser["user"]["id"];
    $user->nickname = $SceneIDuser["user"]["display_name"];
    $user->avatar = $a;

    $user->Create();

    $user = PouetUser::Spawn( $user->id );
  }

  if ( $user->IsBanned() )
  {
		redirect("error.php?e=".rawurlencode("We dun like yer type 'round these parts."));
  }

  $_SESSION["user"] = $user;
  
  $currentUserSettings = SQLLib::SelectRow(sprintf_esc("select * from usersettings where id=%d",$user->id));
  $ephemeralStorage->set( "settings:".$user->id, $currentUserSettings );

  redirect( basename( $returnURL ? $returnURL : "index.php" ) );
  
}
catch(SceneID3Exception $e) 
{
	redirect("error.php?e=".rawurlencode( $e->GetMessage() ));
}

?>
