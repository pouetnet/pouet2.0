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

function move_uploaded_file_fake( $src, $dst )
{
  if (!is_uploaded_file($src)) return false;
  
  copy( $src, $dst );
  unlink( $src );
  
  return true;
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
  $path = (substr(POUET_ROOT_URL,0,4) == "https") ? "https://" : "http://";
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
  global $DEFAULT_USERSETTINGS;
  if ($_SESSION["settings"])
    return $_SESSION["settings"]->$s;
  else
    return $DEFAULT_USERSETTINGS->$s;
}

function find_screenshot( $id )
{
  $ext = array(".jpg",".gif",".png");
  foreach ($ext as $e) {
    $p = "screenshots/".(int)$id.$e;
    if(file_exists(POUET_CONTENT_LOCAL . "/" . $p)) 
      return $p;
  }
  return NULL;
}

function get_local_screenshot_path( $id, $ext )
{
  return sprintf(POUET_CONTENT_LOCAL . "screenshots/%d.%s",$id,$ext);
}

function get_local_nfo_path( $id )
{
  return sprintf(POUET_CONTENT_LOCAL . "nfo/%d.nfo",$id);
}

function get_local_partyresult_path( $id, $year )
{
  return sprintf(POUET_CONTENT_LOCAL . "results/%d_%02d.txt",$id,$year%100);
}

function get_local_bbsnfo_path( $id )
{
  return sprintf(POUET_CONTENT_LOCAL . "othernfo/%d.nfo",$id);
}

define("NO_PARTY_ID",1024);
define("POUET_CACHE_MAX",25);
if (POUET_TEST)
  define("POUET_CDC_MINGLOP",4);
else
  define("POUET_CDC_MINGLOP",64);
define("POUET_EARLIEST_YEAR",1980);

?>
