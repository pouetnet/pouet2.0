<?php
class BenchTimer
{
  function __construct( $name = "" )
  {
    $this->start = microtime_float();
    $this->name = $name ?: "bench";
  }
  function __destruct()
  {
    printf("<!-- %s = %.4f sec -->\n",$this->name,microtime_float() - $this->start);
  }
};

function microtime_float()
{
  return microtime(true);
}

/*
 * wraps long words without cutting html tag arguments
 *
 * WARNING - THIS IS LEGACY CODE AND MIGHT CAUSE PROBLEMS  // garg
 */
function better_wordwrap( $str, $cols = 80, $cut = "\n" )
{
  $encoding = "utf-8";

  $tag_open = '<';
  $tag_close = '>';
  
  $lines = preg_split("/\n/",$str);
  $out = array();
  foreach($lines as $line)
  {
    if (mb_strlen($line,$encoding) <= $cols)
    {
      $out[] = $line;
      continue;
    }
    
    $i = 0;
    $in_tag = 0;
    $segment_width = 0;
    $chrArray = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY);
    foreach($chrArray as $char)
    {
      if ($char == $tag_open) 
      {
        $in_tag++;
      }
      else if ($char == $tag_close) 
      {
        if ($in_tag > 0) 
        {
          $in_tag--;
        }
      } 
      else 
      {
        if ($in_tag == 0) 
        {
          if($char != ' ') 
          {
            $segment_width++;
            if ($segment_width > $cols) 
            {
              $line = mb_substr($line,0,$i,$encoding) . $cut . mb_substr($line,$i,null,$encoding);
              $i += mb_strlen($cut,$encoding);
              $line_len = mb_strlen($line,$encoding);
              $segment_width = 0;
            }
          } 
          else 
          {
            $segment_width = 0;
          }
        }
      }
      $i++;
    }
    $out[] = $line;
  }
  return implode("\n",$out);
}

function toObject($array) {
  $obj = new stdClass();
  foreach ($array as $key => $val) {
    $obj->$key = is_array($val) ? toObject($val) : $val;
  }
  return $obj;
}

function secToReadable($dif, $toDays)
{
  $s = "";
  if ($toDays)
  {
    $dif = (int)($dif / (60 * 60 * 24));
    $s = ($dif % 30) . "d"      ; $dif = (int)($dif / 30); if (!$dif) return $s;
    $s = ($dif % 12) . "m " . $s; $dif = (int)($dif / 12); if (!$dif) return $s;
    $s = $dif . "y " . $s;
  }
  else
  {
    $s = ($dif % 60) . "s"      ; $dif = (int)($dif / 60); if (!$dif) return $s;
    $s = ($dif % 60) . "m " . $s; $dif = (int)($dif / 60); if (!$dif) return $s;
    $s = ($dif % 24) . "h " . $s; $dif = (int)($dif / 24); if (!$dif) return $s;
    $s = $dif . "d " . $s;
  }
  return $s;
}

function dateDiffReadable( $a, $b )
{
  if (is_string($a)) $a = strtotime($a);
  if (is_string($b)) $b = strtotime($b);

  return secToReadable($a - $b, false);
}

function dateDiffReadableDays( $a, $b )
{
  if (is_string($a)) $a = strtotime($a);
  if (is_string($b)) $b = strtotime($b);

  return secToReadable($a - $b, true);
}

function cdcstack($n) 
{ // by ryg
  if (!$n) return;

  printf("<div class='cdcstack' title='%d CDCs'>",$n);
  for ($x=0; $x<$n; $x++) echo "<span></span>";
  echo "</div>";

  /*
  OLD IMG VERSION
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
  */
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
  if (!$ref)
  {
    return true;
  }
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
  if (!$date || $date[0]=="0") return "";
  if (substr($date,5,2)=="00")
    return substr($date,0,4);
  return strtolower(date("F Y",strtotime($date)));
}

function array_select( $array, $keys )
{
  $out = array();
  foreach($keys as $v)
    if (@$array[$v]) $out[$v] = $array[$v];
  return $out;
}

function shortify( $text, $length = 100 )
{
  if (mb_strlen($text?:"","utf-8") <= $length) return $text;
  $z = mb_stripos($text," ",$length-3,"utf-8");
  return mb_substr($text,0,$z?$z:$length-3,"utf-8")."...";
}

function shortify_cut( $text, $length = 100 )
{
  if (mb_strlen($text,"utf-8") <= $length) return $text;
  //$z = mb_stripos($text," ",$length);
  return mb_substr($text,0,$length-3,"utf-8")."...";
}

function parse_message( $p )
{
  $p = htmlspecialchars($p,ENT_QUOTES);
  $p = bbencode($p,true);
  $p = nl2br($p);
  $p = preg_replace_callback("/<code>(.*?)<\/code>/ims",function($s){
    return str_replace("<br />","",$s[0]);
  },$p);
  //$p = better_wordwrap($p,80," ");
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

function cp437_to_utf8( $text )
{
  // from ftp://www.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/PC/CP437.TXT
  $CP437_to_utf8 = array(
    "\x80" => "\xc3\x87", "\x81" => "\xc3\xbc", "\x82" => "\xc3\xa9", "\x83" => "\xc3\xa2",
    "\x84" => "\xc3\xa4", "\x85" => "\xc3\xa0", "\x86" => "\xc3\xa5", "\x87" => "\xc3\xa7", "\x88" => "\xc3\xaa",
    "\x89" => "\xc3\xab", "\x8a" => "\xc3\xa8", "\x8b" => "\xc3\xaf", "\x8c" => "\xc3\xae", "\x8d" => "\xc3\xac",
    "\x8e" => "\xc3\x84", "\x8f" => "\xc3\x85", "\x90" => "\xc3\x89", "\x91" => "\xc3\xa6", "\x92" => "\xc3\x86",
    "\x93" => "\xc3\xb4", "\x94" => "\xc3\xb6", "\x95" => "\xc3\xb2", "\x96" => "\xc3\xbb", "\x97" => "\xc3\xb9",
    "\x98" => "\xc3\xbf", "\x99" => "\xc3\x96", "\x9a" => "\xc3\x9c", "\x9b" => "\xc2\xa2", "\x9c" => "\xc2\xa3",
    "\x9d" => "\xc2\xa5", "\x9e" => "\xe2\x82\xa7", "\x9f" => "\xc6\x92", "\xa0" => "\xc3\xa1", "\xa1" => "\xc3\xad", 
    "\xa2" => "\xc3\xb3", "\xa3" => "\xc3\xba", "\xa4" => "\xc3\xb1", "\xa5" => "\xc3\x91", "\xa6" => "\xc2\xaa",
    "\xa7" => "\xc2\xba", "\xa8" => "\xc2\xbf", "\xa9" => "\xe2\x8c\x90", "\xaa" => "\xc2\xac", "\xab" => "\xc2\xbd",
    "\xac" => "\xc2\xbc", "\xad" => "\xc2\xa1", "\xae" => "\xc2\xab", "\xaf" => "\xc2\xbb", "\xb0" => "\xe2\x96\x91",
    "\xb1" => "\xe2\x96\x92", "\xb2" => "\xe2\x96\x93", "\xb3" => "\xe2\x94\x82", "\xb4" => "\xe2\x94\xa4",
    "\xb5" => "\xe2\x95\xa1", "\xb6" => "\xe2\x95\xa2", "\xb7" => "\xe2\x95\x96", "\xb8" => "\xe2\x95\x95",
    "\xb9" => "\xe2\x95\xa3", "\xba" => "\xe2\x95\x91", "\xbb" => "\xe2\x95\x97", "\xbc" => "\xe2\x95\x9d",
    "\xbd" => "\xe2\x95\x9c", "\xbe" => "\xe2\x95\x9b", "\xbf" => "\xe2\x94\x90", "\xc0" => "\xe2\x94\x94",
    "\xc1" => "\xe2\x94\xb4", "\xc2" => "\xe2\x94\xac", "\xc3" => "\xe2\x94\x9c", "\xc4" => "\xe2\x94\x80",
    "\xc5" => "\xe2\x94\xbc", "\xc6" => "\xe2\x95\x9e", "\xc7" => "\xe2\x95\x9f", "\xc8" => "\xe2\x95\x9a",
    "\xc9" => "\xe2\x95\x94", "\xca" => "\xe2\x95\xa9", "\xcb" => "\xe2\x95\xa6", "\xcc" => "\xe2\x95\xa0",
    "\xcd" => "\xe2\x95\x90", "\xce" => "\xe2\x95\xac", "\xcf" => "\xe2\x95\xa7", "\xd0" => "\xe2\x95\xa8",
    "\xd1" => "\xe2\x95\xa4", "\xd2" => "\xe2\x95\xa5", "\xd3" => "\xe2\x95\x99", "\xd4" => "\xe2\x95\x98",
    "\xd5" => "\xe2\x95\x92", "\xd6" => "\xe2\x95\x93", "\xd7" => "\xe2\x95\xab", "\xd8" => "\xe2\x95\xaa",
    "\xd9" => "\xe2\x94\x98", "\xda" => "\xe2\x94\x8c", "\xdb" => "\xe2\x96\x88", "\xdc" => "\xe2\x96\x84",
    "\xdd" => "\xe2\x96\x8c", "\xde" => "\xe2\x96\x90", "\xdf" => "\xe2\x96\x80", "\xe0" => "\xce\xb1",
    "\xe1" => "\xc3\x9f", "\xe2" => "\xce\x93", "\xe3" => "\xcf\x80", "\xe4" => "\xce\xa3", "\xe5" => "\xcf\x83",
    "\xe6" => "\xc2\xb5", "\xe7" => "\xcf\x84", "\xe8" => "\xce\xa6", "\xe9" => "\xce\x98", "\xea" => "\xce\xa9",
    "\xeb" => "\xce\xb4", "\xec" => "\xe2\x88\x9e", "\xed" => "\xcf\x86", "\xee" => "\xce\xb5", "\xef" => "\xe2\x88\xa9",
    "\xf0" => "\xe2\x89\xa1", "\xf1" => "\xc2\xb1", "\xf2" => "\xe2\x89\xa5", "\xf3" => "\xe2\x89\xa4",
    "\xf4" => "\xe2\x8c\xa0", "\xf5" => "\xe2\x8c\xa1", "\xf6" => "\xc3\xb7", "\xf7" => "\xe2\x89\x88",
    "\xf8" => "\xc2\xb0", "\xf9" => "\xe2\x88\x99", "\xfa" => "\xc2\xb7", "\xfb" => "\xe2\x88\x9a",
    "\xfc" => "\xe2\x81\xbf", "\xfd" => "\xc2\xb2", "\xfe" => "\xe2\x96\xa0", "\xff" => "\xc2\xa0"
  );

  // we use preg_replace because str_replace falls victim to continually subtituting until it finds a match even for the new substitute   
  // i.e. if the map is a=>ab, b=>bc, it will turn "a" into "abc"
  return preg_replace_callback("/([\x80-\xff])/",function($m)use($CP437_to_utf8){ return $CP437_to_utf8[$m[1]]; },$text);
}

function process_ascii( $text, $enc = null )
{
  if ($enc == null)
  {
    $enc = mb_detect_encoding( $text, "utf-8, iso-8859-1" );
  }
  if ($enc == "cp437")
  {
    return cp437_to_utf8($text);
  }
  return mb_convert_encoding( $text, "utf-8", $enc );
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
  if(strstr($myurl["host"],"docs.google") 
  || strstr($myurl["host"],"drive.google")
  || strstr($myurl["host"],"dropbox.com")
  || strstr($myurl["host"],"dropboxusercontent.com")
  || strstr($myurl["host"],"1drv.ms")
  || strstr($myurl["host"],"onedrive.live.com"))
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
    "mega.co.nz",
    "mega.nz",
    "mega.io",
  );
  foreach ($shithosts as $v)
    if(strstr($myurl["host"],$v))
      $errormessage[] = "seriously, get better hosting - read the FAQ on how!";

  if(strstr($myurl["path"],"incoming/"))
    $errormessage[] = "the file you submitted is in an incoming path, try to find a real path";
  if(strstr($myurl["host"],"scene.org") && strstr(@$myurl["query"]?:"","incoming/"))
    $errormessage[] = "the file you submitted is in an incoming path, try to find a real path";
  if( (((@$myurl["port"])!=80) && ((@$myurl["port"])!=0)) && ((strlen(@$myurl["user"])>0) || (strlen(@$myurl["pass"])>0)) )
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
    /*
    // [1:04 AM] scamp: @Gargaj https://ftp/... is fine, as if I ever would have/need mirrors again that would work on all of them.
    if ($myurl["scheme"]=="http")
     $errormessage[] = "scamp says: no link to untergrund.net via http please!";
    */
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
  $query = array_merge(@$_GET,$param);
  unset( $query["reverse"] );
  if(@$param["order"] && @$_GET["order"] == @$param["order"] && !@$_GET["reverse"])
    $query["reverse"] = 1;
  $url = parse_url($_SERVER["REQUEST_URI"]);
  return _html( $url["path"] . "?" . http_build_query($query));
}

function softurlencode($string) 
{
  $replacements = array(  '!',   '*',   "'",   "(",   ")",   ";",   ":",   "@",   "&",   "=",   "+",   "$",   ",",   "/",   "?", /*  "%", */  "#",   "[",   "]");
  $entities     = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', /*'%25', */'%23', '%5B', '%5D');
  return str_replace($replacements, $entities, $string);
}
function verysofturlencode($string) 
{
  $replacements = array(  '!',   '*',   "'",   "(",   ")",   ";",    "@",   "+",  " ",   "$",   ",",    "#",   "[",   "]");
  $entities     = array('%21', '%2A', '%27', '%28', '%29', '%3B',  '%40', '%2B', '%20', '%24', '%2C',  '%23', '%5B', '%5D');
  return str_replace($replacements, $entities, $string);
}
function handle_db_cache( $filename, $funcGenerateIfNotFound )
{
  if (!file_exists( $filename ))
  {
    $data = $funcGenerateIfNotFound();
    file_put_contents($filename,serialize($data));
    return $data;
  }
  return unserialize( file_get_contents( $filename ) );  
}

$MAX_PROD_VIEWS = null;
$MAX_PROD_VIEWS_LOG = null;
function calculate_popularity( $views )
{
  global $MAX_PROD_VIEWS;
  global $MAX_PROD_VIEWS_LOG;
  
  $minViews = 100;
  if (!$MAX_PROD_VIEWS)
  {
    $MAX_PROD_VIEWS = max( 0, SQLLib::SelectRow("SELECT MAX(views) as m FROM prods")->m - $minViews );
    $MAX_PROD_VIEWS_LOG = log10($MAX_PROD_VIEWS);
  }
  
  return log10(max( 1, $views - $minViews )) / $MAX_PROD_VIEWS_LOG * 100.0;
}

function progress_bar( $val, $title = "" )
{
  return "<div class='outerbar' title='"._html($title)."'><div class='innerbar' style='width: ".$val."%'>&nbsp;<span>"._html($title)."</span></div></div>\n";
}

function progress_bar_solo( $val, $title = "" )
{
  return "<div class='innerbar_solo' style='width: ".$val."px' title='"._html($title)."'>&nbsp;<span>"._html($title)."</span></div>";
}

function hashify($s) {
  $hash = strtolower($s);
  $hash = preg_replace("/[^\w]+/","-",$hash);
  $hash = preg_replace("/^[_]+/","",$hash);
  $hash = preg_replace("/[_]+$/","",$hash);
  $hash = trim($hash,"-");
  return $hash;
}

function enum2array($s)
{
  return str_getcsv(substr($s,substr($s,0,3)=="set"?4:5,-1), ',', "'");
}

function is_string_meaningful($s)
{
  $message = $s;
  //$message = str_replace(html_entity_decode('&shy;', 0, 'UTF-8'),"",$message);
  $message = preg_replace('/\p{C}+/u', "", $message);
  $message = htmlspecialchars($message,ENT_QUOTES);
  $message = bbencode($message);
  $message = strip_tags($message,"<img>");
  $message = trim($message);
  return !!$message;
}

function array_diff_meaningful($new,$old)
{
  $out = array();
  foreach($new as $k=>$v)
  {
    // collect values if they are different and either one of them are not 0/null/empty/...
    if( (@$new[$k] != @$old[$k]) && (@$new[$k] && @$old[$k]) || (@$new[$k] && !@$old[$k]) || (!@$new[$k] && @$old[$k]) )
    {
      $out[$k] = $v;
    }    
  }
  return $out;
}

function flush_cache( $filename, $condition = null )
{
  $fullPath = POUET_ROOT_LOCAL . "/cache/" . $filename;
  
  if ($condition)
  {
    $data = @unserialize(@file_get_contents($fullPath));
    if ($data)
    {
      foreach($data as $v)
      {
        if ($condition($v))
        {
          @unlink( $fullPath );
          return true;
        }
      }
    }
    return false;
  }
  else
  {
    @unlink( $fullPath );
    return true;
  }
}

///////////////////////////////////////////////////////////////////////////////

function _html( $s )
{
  return htmlspecialchars($s?:"",ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,"utf-8");
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
  return @$_SESSION["user"] ? $_SESSION["user"]->id : 0;
}

function get_login_level()
{
  return @$_SESSION["user"] ? $_SESSION["user"]->level : false;
}

function get_setting( $s )
{
  global $currentUserSettings;
  return $currentUserSettings->$s;
}

function find_screenshot( $id )
{
  $id = (int)$id;
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
  $id = (int)$id;
  $newPath = sprintf(POUET_CONTENT_LOCAL . "files/screenshots/%05d/%08d.%s",(int)($id/1000),$id,$ext);
  return $newPath;
}

function get_local_nfo_path( $id )
{
  $id = (int)$id;
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
  $id = (int)$id;
  $newPath = sprintf(POUET_CONTENT_LOCAL . "files/nfo_bbs/%05d/%08d.txt",(int)($id/1000),$id);
  return $newPath;
}

function get_screenshot_url( $id, $ext )
{
  $id = (int)$id;
  return sprintf(POUET_CONTENT_URL . "files/screenshots/%05d/%08d.%s",(int)($id/1000),$id,$ext);
}

function get_nfo_url( $id )
{
  $id = (int)$id;
  return sprintf(POUET_CONTENT_URL . "files/nfos/%05d/%08d.txt",(int)($id/1000),$id);
}

function get_partyresult_url( $id, $year )
{
  return sprintf(POUET_CONTENT_URL . "files/results/%04d/%08d.txt",$year,$id);
}

function get_boardnfo_url( $id )
{
  $id = (int)$id;
  return sprintf(POUET_CONTENT_URL . "files/nfo_bbs/%05d/%08d.txt",(int)($id/1000),$id);
}

define("FIXMETHREAD_ID",1024);
define("NO_PARTY_ID",1024);
define("POUET_CACHE_MAX",25);
if (POUET_TEST)
  define("POUET_CDC_MINGLOP",4);
else
  define("POUET_CDC_MINGLOP",64);
define("POUET_EARLIEST_YEAR",1970);

?>
