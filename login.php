<?
require_once("bootstrap.inc.php");
require_once("include_pouet/pouet-user.php");

$csrf = new CSRFProtect();
if (!$csrf->ValidateToken())
  redirect("error.php?e=".rawurlencode("Who are you and where did you come from ?"));
  
$_SESSION = array();

$rv = null;
$err = "";
try
{
  $rv = $sceneID->login( $_POST["login"], $_POST["password"], $_POST["permanent"]=="on")->asAssoc();
} catch(SceneIdException $e) {
  $err = "[SceneID error] ".$e->GetMessage();
}

switch( (int)$rv["returnCode"] )
{
	case 30: {

    $user = PouetUser::Spawn( (int)$rv["user"]["id"] );
    if (!$user || !$user->id) 
    {
      $entry = glob("./avatars/*.gif");
      $r = $entry[array_rand($entry)];
      $a = str_replace("./avatars/","",$r);
    
      $user = new PouetUser();
      $user->id = (int)$rv["user"]["id"];
      $user->nickname = $rv["user"]["nickname"];
      $user->avatar = $a;
            
      $user->Create();

      $user = PouetUser::Spawn( $user->id );
    }
    
    $allowed = array(1007,30761);
    if (!POUET_TEST && array_search($user->id,$allowed)===false)
    {
  		redirect("error.php?e=".rawurlencode("Sorry, we're in debugmode still :/"));
    }
    else if ( $user->level == "banned" )
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
	} break;

	case NULL:
	case FALSE:
	case -1: {
		redirect("error.php?e=".rawurlencode($err ? $err : "Couldn't connect SceneID. :("));
	} break;

	default: {
		redirect("error.php?e=".rawurlencode($rv["returnMessage"]));
	} break;
}
?>
