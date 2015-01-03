<?
function microtime_float()
{
  return microtime(true);
}

/*
 * wraps long words without cutting html tag arguments
 *
 * WARNING - THIS IS LEGACY CODE AND MIGHT CAUSE PROBLEMS  // garg
 */
function better_wordwrap($str,$cols,$cut){
  $encoding = "utf-8";

	$tag_open = '<';
	$tag_close = '>';
	$count = 0;
	$in_tag = 0;
	$str_len = mb_strlen($str,$encoding);
	$segment_width = 0;

	for ($i=0; $i<=$str_len; $i++){
		if ($str{$i} == $tag_open) {
			$in_tag++;
		} elseif ($str{$i} == $tag_close) {
			if ($in_tag > 0) {
				$in_tag--;
				$segment_width = 0;
			}
		} else {
			if ($in_tag == 0) {
				if($str{$i} != ' ') {
					$segment_width++;
					if ($segment_width > $cols) {
						 $str = mb_substr($str,0,$i,$encoding).$cut.mb_substr($str,$i,$str_len,$encoding);
						 $i += mb_strlen($cut,$encoding);
						 $str_len = mb_strlen($str,$encoding);
						 $segment_width = 0;
					}
				} else {
					$segment_width = 0;
				}
			}
		}
	}
	return $str;
}

function toObject($array) {
  $obj = new stdClass();
  foreach ($array as $key => $val) {
    $obj->$key = is_array($val) ? toObject($val) : $val;
  }
  return $obj;
}

function dateDiffReadable( $a, $b )
{
  if (is_string($a)) $a = strtotime($a);
  if (is_string($b)) $b = strtotime($b);

  $dif = $a - $b;

  $s = ($dif % 60) . "s"      ; $dif = (int)($dif / 60); if (!$dif) return $s;
  $s = ($dif % 60) . "m " . $s; $dif = (int)($dif / 60); if (!$dif) return $s;
  $s = ($dif % 24) . "h " . $s; $dif = (int)($dif / 24); if (!$dif) return $s;
  $s = $dif . "d " . $s;
  return $s;
}

function dateDiffReadableDays( $a, $b )
{
  if (is_string($a)) $a = strtotime($a);
  if (is_string($b)) $b = strtotime($b);

  $dif = ($a - $b) / (60 * 60 * 24);

  $s = ($dif % 30) . "d"      ; $dif = (int)($dif / 30); if (!$dif) return $s;
  $s = ($dif % 12) . "m " . $s; $dif = (int)($dif / 12); if (!$dif) return $s;
  $s = $dif . "y " . $s;
  return $s;
}

function cdcstack($n) { // by ryg
  if (!$n) return;
  echo "<div class='cdcstack'>";
  if ($n==1) {
    echo "<img src='".POUET_CONTENT_URL."gfx/titles/coupdecoeur.gif' title='cdc' alt='cdc'>";
  } else {
    echo "<img src='".POUET_CONTENT_URL."gfx/cdcstack_start.gif' title='".$n." cdcs' alt='".$n." cdcs'>";
    for ($x=0; $x<$n-2; $x++) {
      echo "<img src='".POUET_CONTENT_URL."gfx/cdcstack_loop.gif' title='".$n." cdcs' alt='".$n." cdcs'>";
    }
    echo "<img src='".POUET_CONTENT_URL."gfx/cdcstack_end.gif' title='".$n." cdcs' alt='".$n." cdcs'>";
  }
  echo "</div>";
}

function gloperator_log( $itemType, $itemID, $action, $additionalData = array() )
{
  global $currentUser;

  $sql = array();
  $sql["gloperatorID"] = $currentUser->id;
  $sql["itemID"] = $itemID;
  $sql["itemType"] = $itemType;
  $sql["action"] = $action;
  $sql["date"] = date("Y-m-d H:i:s");
  $sql["additionalData"] = json_encode($additionalData);
  SQLLib::InsertRow("gloperator_log",$sql);
}

// i'm not even sure how much of this is even valid --garg
function CheckReferrer( $ref )
{
  $myurl=parse_url($ref);
  if(strstr($myurl["host"],"farb-rausch.de")) return false;
  if(strstr($myurl["host"],"flipcode.com")) return false;
  if(strstr($myurl["host"],"0ccult.de")) return false;
  if(strstr($myurl["host"],"ypocamp.fr")) return false;
  if(strstr($myurl["host"],"chanka.emulatronia.com")) return false;
  if(strstr($myurl["host"],"images.google")) return false;
  if(strstr($myurl["host"],"urlreload")) return false;
  if(strlen($ref)<2) return false;
  return true;
}

// the reason we need this is because the "normal" move_uploaded_file function
// breaks the ACL flags: https://bugs.php.net/bug.php?id=65057
function move_uploaded_file_fake( $src, $dst )
{
  if (!is_uploaded_file($src)) return false;
  
  $dir = dirname($dst);
  if (!file_exists($dir))
  {
    @mkdir($dir);
    @chmod($dir,0775);
  }

  copy( $src, $dst );
  unlink( $src );

  return true;
}

function has_trait($object,$trait)
{
  return (array_search($trait,class_uses($object))!==false);
}

function renderHalfDate($date)
{
  if (!$date || $date{0}=="0") return "";
  if (substr($date,5,2)=="00")
    return substr($date,0,4);
  return strtolower(date("F Y",strtotime($date)));
}

function array_select( $array, $keys )
{
  $out = array();
  foreach($keys as $v)
    if ($array[$v]) $out[$v] = $array[$v];
  return $out;
}

function shortify( $text, $length = 100 )
{
  if (mb_strlen($text,"utf-8") <= $length) return $text;
  $z = mb_stripos($text," ",$length,"utf-8");
  return mb_substr($text,0,$z?$z:$length,"utf-8")."...";
}

function shortify_cut( $text, $length = 100 )
{
  if (mb_strlen($text,"utf-8") <= $length) return $text;
  //$z = mb_stripos($text," ",$length);
  return mb_substr($text,0,$length,"utf-8")."...";
}

function parse_message( $p )
{
  $p = htmlspecialchars($p,ENT_QUOTES);
  $p = bbencode($p,true);
  $p = nl2br($p);
  $p = preg_replace_callback("/<code>(.*?)<\/code>/ims",function($s){
    return str_replace("<br />","",$s[0]);
  },$p);
  $p = better_wordwrap($p,80," ");
  return $p;
}

function selfPath()
{
  $path = $_SERVER["SCRIPT_NAME"];
  if ($_SERVER["QUERY_STRING"])
    $path .= "?" . $_SERVER["QUERY_STRING"];
  return $path;
}
function rootRelativePath()
{
  $path = (substr(POUET_ROOT_URL,0,5) == "https") ? "https://" : "http://";
  $path .= $_SERVER["HTTP_HOST"];
  $path .= $_SERVER["REQUEST_URI"];
  return substr($path,strlen(POUET_ROOT_URL));
}

function split_search_terms( $str )
{
  preg_match_all('/([^\s"]+)|"([^"]*)"/',$str,$m);
  $terms = array();
  foreach($m[0] as $k=>$v)
    $terms[] = $m[1][$k] ? $m[1][$k] : $m[2][$k];
  return $terms;
}

function process_ascii( $text )
{
  $enc = mb_detect_encoding( $text, "iso-8859-1,utf-8" );
  $utf8 = mb_convert_encoding( $text, "utf-8", $enc );
  return $utf8;
}

function sideload( $url, $options = array() )
{
  $curl = curl_init();

  $header = array();

  curl_setopt($curl, CURLOPT_URL, $url);
  @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  if ($options["connectTimeout"])
    curl_setopt($curl, CURLOPT_TIMEOUT, (int)$options["connectTimeout"]);
  curl_setopt($curl, CURLOPT_NOPROGRESS, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

  $html = curl_exec($curl);
  curl_close($curl);

  return $html;
}

function validateLink( $url )
{
  $errormessage = array();

  if(!$url)
  {
	  $errormessage[]="no link?!";
	  return $errormessage;
	}

  $myurl=parse_url($url);

  if(strlen($myurl["host"])==0)
    $errormessage[] = "missing hostname in the download link";
  if(strstr($myurl["host"],".")===false)
    $errormessage[] = "invalid hostname";

  if(strstr($myurl["host"],"back2roots"))
    $errormessage[] = "back2roots does not allow download from outside, find another host please";
  if(strstr($myurl["host"],"intro-inferno"))
    $errormessage[] = "\"stop linking to intro-inferno, you turds :)\" /reed/";

  if(strstr($myurl["host"],"geocities"))
    $errormessage[] = "please get proper hosting (e.g. untergrund or scene.org) without traffic limits";
  if(strstr($myurl["host"],"docs.google"))
    $errormessage[] = "please get proper, permanent hosting";

  $shithosts = array(
    "rapidshare",
    "depositfiles",
    "megaupload",
    "filefactory",
    "sendspace",
    "netload",
    "mediafire",
    "megashare",
    "uploading.com",
    "mirrorcreator",
    "multiupload",
    "tinyurl",
    "bit.ly",
  );
  foreach ($shithosts as $v)
    if(strstr($myurl["host"],$v))
      $errormessage[] = "seriously, get better hosting - read the FAQ on how!";

  if(strstr($myurl["path"],"incoming"))
    $errormessage[] = "the file you submitted is in an incoming path, try to find a real path";
  if(strstr($myurl["host"],"scene.org") && strstr($myurl["query"],"incoming"))
    $errormessage[] = "the file you submitted is in an incoming path, try to find a real path";
  if( ((($myurl["port"])!=80) && (($myurl["port"])!=0)) && ((strlen($myurl["user"])>0) || (strlen($myurl["pass"])>0)) )
    $errormessage[] = "no private FTP please";

  return $errormessage;
}
function validateDownloadLink( $url )
{
  if(!$url)
  {
	  return array("no download link?!");
	}

  $errormessage = validateLink( $url );

  $myurl=parse_url($url);
  if(($myurl["scheme"]!="http")&&($myurl["scheme"]!="ftp")&&($myurl["scheme"]!="https"))
    $errormessage[] = "only http/https and ftp protocols are supported for the download link";

  if(strstr($myurl["host"],"youtube") || strstr($myurl["host"],"youtu.be"))
    $errormessage[] = "FUCK YOUTUBE - BINARY OR GTFO";

  // ** apparently this is needed for csdb - still think its a bad idea
  //if(strstr($myurl["path"],".php") && !strstr($myurl["host"],"scene.org"))
  //  $errormessage[] = "please link to the file directly";

  if(strstr($myurl["path"],".txt"))
    $errormessage[] = "NO TEXTFILES.";

  if(strstr($myurl["host"],"untergrund.net"))
  {
    for ($x=1; $x<=5; $x++)
     if(strstr($myurl["host"],"ftp".$x.".untergrund.net"))
      $errormessage[] = "scamp says: link to ftp.untergrund.net not ftp".$x.".untergrund.net!!";
    if ($myurl["scheme"]=="http")
     $errormessage[] = "scamp says: no link to untergrund.net via http please!";
    if(strstr($myurl["host"],"www.untergrund.net"))
     $errormessage[] = "scamp says: godverdom!! link to ftp.untergrund.net instead!";
  }
  if(!basename($myurl["path"]))
    $errormessage[] = "no file? no prod!";

  return $errormessage;
}

function adjust_query( $param ) 
{
  $query = array_merge($_GET,$param);
  $url = parse_url($_SERVER["REQUEST_URI"]);
  return _html( $url["path"] . "?" . http_build_query($query));
}
function adjust_query_header( $param ) 
{
  $query = array_merge($_GET,$param);
  unset( $query["reverse"] );
  if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
    $query["reverse"] = 1;
  $url = parse_url($_SERVER["REQUEST_URI"]);
  return _html( $url["path"] . "?" . http_build_query($query));
}

///////////////////////////////////////////////////////////////////////////////

function _html( $s )
{
  return htmlspecialchars($s,ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,"utf-8");
}
function _js( $s )
{
  return addcslashes( $s, "\x00..\x1f'\"\\/" );
}
function _like( $s )
{
  return addcslashes($s,"%_");
}

// this helps turning "empty" values into null (for foreign keys)
// makes the code more "readable"
function nullify( $v )
{
  return $v ? $v : NULL;
}

function redirect($path)
{
  header("Location: ".POUET_ROOT_URL.$path);
  exit();
}

function get_login_id()
{
  return $_SESSION["user"] ? $_SESSION["user"]->id : 0;
}

function get_login_level()
{
  return $_SESSION["user"] ? $_SESSION["user"]->level : false;
}

function get_setting( $s )
{
  global $currentUserSettings;
  return $currentUserSettings->$s;
}

function find_screenshot( $id )
{
  $extensions = array("jpg","gif","png");
  foreach ($extensions as $ext) 
  {
    $newPath = sprintf("files/screenshots/%05d/%08d.%s",(int)($id/1000),$id,$ext);
    if (file_exists(POUET_CONTENT_LOCAL . $newPath))
      return $newPath;
  }
  return NULL;
}

function get_local_screenshot_path( $id, $ext )
{
  $newPath = sprintf(POUET_CONTENT_LOCAL . "files/screenshots/%05d/%08d.%s",(int)($id/1000),$id,$ext);
  return $newPath;
}

function get_local_nfo_path( $id )
{
  $newPath = sprintf(POUET_CONTENT_LOCAL . "files/nfos/%05d/%08d.txt",(int)($id/1000),$id);
  return $newPath;
}

function get_local_partyresult_path( $id, $year )
{
  $newPath = sprintf(POUET_CONTENT_LOCAL . "files/results/%04d/%08d.txt",$year,$id);
  return $newPath;
}

function get_local_boardnfo_path( $id )
{
  $newPath = sprintf(POUET_CONTENT_LOCAL . "files/nfo_bbs/%05d/%08d.txt",(int)($id/1000),$id);
  return $newPath;
}

function get_screenshot_url( $id, $ext )
{
  return sprintf(POUET_CONTENT_URL . "files/screenshots/%05d/%08d.%s",(int)($id/1000),$id,$ext);
}

function get_nfo_url( $id )
{
  return sprintf(POUET_CONTENT_URL . "files/nfos/%05d/%08d.txt",(int)($id/1000),$id);
}

function get_partyresult_url( $id, $year )
{
  return sprintf(POUET_CONTENT_URL . "files/results/%04d/%08d.txt",$year,$id);
}

function get_boardnfo_url( $id )
{
  return sprintf(POUET_CONTENT_URL . "files/nfo_bbs/%05d/%08d.txt",(int)($id/1000),$id);
}

define("FIXMETHREAD_ID",1024);
define("NO_PARTY_ID",1024);
define("POUET_CACHE_MAX",25);
if (POUET_TEST)
  define("POUET_CDC_MINGLOP",4);
else
  define("POUET_CDC_MINGLOP",64);
define("POUET_EARLIEST_YEAR",1980);

?>
