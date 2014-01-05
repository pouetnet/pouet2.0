<?
error_reporting(E_ALL & ~E_NOTICE);

define("POUET_ROOT_LOCAL",dirname(__FILE__));
if (!file_exists(POUET_ROOT_LOCAL . "/include_generic/credentials.inc.php"))
  die("Please create an include_generic/credentials.inc.php - you can use the credentials.inc.php.dist as an example");

require_once( POUET_ROOT_LOCAL . "/include_generic/credentials.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_generic/sqllib.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_generic/sceneid.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_generic/functions.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_generic/libbb.php");
require_once( POUET_ROOT_LOCAL . "/include_generic/orm.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_generic/formifier.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_generic/LastRss.php");
require_once( POUET_ROOT_LOCAL . "/include_generic/csrf.inc.php");

require_once( POUET_ROOT_LOCAL . "/include_pouet/enums.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/request-classes.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-box.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-prod.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-user.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-party.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-group.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-formprocessor.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-paginator.php");

if (POUET_TEST)
  SQLLib::$debugMode = true;

$lifetime = 60 * 60 * 24 * 365;
@ini_set('session.cookie_lifetime', $lifetime);

session_name("POUETSESS2");
session_set_cookie_params($lifetime, POUET_ROOT_PATH, POUET_COOKIE_DOMAIN);
@session_start();

$sceneID = null;
if (class_exists("SceneId"))
  $sceneID = SceneId::Factory(SCENEID_USER, SCENEID_PASS, SCENEID_URL);

$currentUser = NULL;
if (get_login_id())
{
  $id = get_login_id();

  $host = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
  if ($host!==".")
  {
    SQLLib::Query(sprintf_esc("update users set lastip='%s', lasthost='%s', lastlogin='%s' where id=%d",
        $_SERVER["REMOTE_ADDR"],$host,date("Y-m-d H:i:s"),$id));
    $currentUser = PouetUser::Spawn( $id );
  }
}

if ($currentUser && $currentUser->IsBanned())
{
  $_SESSION = $currentUser = NULL;
}

$_SESSION["keepalive"] = str_pad("",rand(1,10),"x") . rand(1,10000);

$timer["page"]["start"] = microtime_float();

if (!$_SESSION["settings"])
{
  require_once("include_pouet/default_usersettings.php");
  $_SESSION["settings"] = $DEFAULT_USERSETTINGS;
}
?>
