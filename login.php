<?
require_once("bootstrap.inc.php");
require_once("include_pouet/pouet-user.php");

//$csrf = new CSRFProtect();
if ($_GET["error"])
  redirect("error.php?e=".rawurlencode( $_GET["error_description"] ));

if (!$_GET["code"])
{
  $_SESSION["stateTest"] = rand(0,0x7FFFFFFF);
  $sceneID->SetState( $_SESSION["stateTest"] );
  $sceneID->PerformAuthRedirect();
  exit();
}

$rv = null;
$err = "";
try
{
  $sceneID->SetState( $_SESSION["stateTest"] );
  $sceneID->ProcessAuthResponse();

  session_regenerate_id(true);
  $_SESSION = array();

  $user = json_decode( $sceneID->Me() );

  $user = PouetUser::Spawn( (int)$user->user->id );
  if (!$user || !$user->id)
  {
    $entry = glob(POUET_CONTENT_LOCAL."avatars/*.gif");
    $r = $entry[array_rand($entry)];
    $a = basename($r);

    $user = new PouetUser();
    $user->id = (int)$user->user->id;
    $user->nickname = $user->user->username;
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
/*
  setcookie($rv["cookie"]["name"],
            $rv["cookie"]["value"],
            $rv["cookie"]["expires"],
            $rv["cookie"]["path"], "pouet.net");
*/
  redirect( basename($_POST["return"]?$_POST["return"]:"index.php") );
  
}
catch(SceneID3Exception $e) 
{
	redirect("error.php?e=".rawurlencode( $e->GetMessage() ));
}

?>
