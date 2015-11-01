<?
if(php_sapi_name() != "cli")
  die("commandline only!");

// change to pouet root
chdir( dirname( __FILE__ ) );

require_once("bootstrap.inc.php");
require_once("admin.functions.php");

echo "[".date("Y-m-d H:i:s")."] Cron running: ".$argv[1]."\n";

function cron_CheckLinks( $id = null )
{
  $s = new SQLSelect();
  $s->AddField("prods.id");
  $s->AddField("prods.download");
  $s->AddTable("prods");
  $s->AddJoin("left","prods_linkcheck","prods_linkcheck.prodID = prods.id");
  $s->AddOrder("RAND()");
  if ($id)
    $s->AddWhere(sprintf_esc("prods.id = %d",$id));
  else
    $s->AddWhere("prods_linkcheck.testDate is NULL or datediff(now(),prods_linkcheck.testDate) > 30");
  $s->SetLimit( 20 );
  $prods = SQLLib::SelectRows( $s->GetQuery() );
  $out = array();
  foreach($prods as $prod)
  {
    $ch = curl_init();
    $url = verysofturlencode($prod->download);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Pouet-BrokenLinkCheck/2.0");
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);    
    
    curl_exec($ch);
    
    $a = array();
    $a["prodID"] = $prod->id;
    $a["protocol"] = "http";
    $lastUrl = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    if (strpos($lastUrl,"ftp://")===0)
      $a["protocol"] = "ftp";
    $a["testDate"] = date("Y-m-d H:i:s");
    $a["returnCode"] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $a["returnContentType"] = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
    SQLLib::UpdateOrInsertRow("prods_linkcheck",$a,sprintf_esc("prodID=%d",$prod->id));
    
    curl_close($ch);
    
    if ($id)
    {
      $out[] = json_encode($a);
      $out[] = "\n[".$prod->id."] " . $url . " >> ". $a["returnCode"];
    }
    else
    {
      $out[] = $prod->id . " -> " . $a["returnCode"];
    }
    sleep(5);
  }
  return implode(", ",$out);
}

switch($argv[1])
{
  case "recacheTopDemos":
    $content = pouetAdmin_recacheTopDemos();
    preg_match_all("/<h3>(.*)<\/h3>/",$content,$m);
    foreach($m[1] as $v)
      echo " > Recache: ".$v."\n";
    break;
  case "linkCheck":
    $content = cron_CheckLinks( $argv[2] );
    echo " > linkCheck: ".$content."\n";
    break;
}

echo "[".date("Y-m-d H:i:s")."] Cron finished.\n\n";
?>