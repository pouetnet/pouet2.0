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
  $s->AddOrder("prods_linkcheck.testDate");
  $s->AddOrder("RAND()");
  if ($id)
    $s->AddWhere(sprintf_esc("prods.id = %d",$id));
  else
    $s->AddWhere("prods_linkcheck.testDate is NULL or datediff(now(),prods_linkcheck.testDate) > 20");
  $s->SetLimit( 20 );
  $prods = SQLLib::SelectRows( $s->GetQuery() );
  $out = array();
  foreach($prods as $prod)
  {
    $sideload = new Sideload();
    $urls = array();
    $url = verysofturlencode($prod->download);
    for ($x=0; $x<10; $x++)
    {
      $sideload->options["max_length"] = 1024; // abort download after 1k
      $sideload->options["verify_peer"] = false;
      $sideload->options["user_agent"] = "Pouet-BrokenLinkCheck/2.0";
      $sideload->options["method"] = "GET";
      $urls[] = $url;
      $sideload->Request($url);
      
      $lastUrl = $sideload->httpURL;
      if ($lastUrl == $url)
        break;
      $url = $lastUrl;
    }

    // temporary hack for csdb, they tend to occasionally return 503 for
    // links that would normally work just fine
    if ($sideload->httpReturnCode == 503 && strstr($lastUrl,"csdb")!==false)
    {
      continue;
    }
    
    $a = array();
    $a["prodID"] = $prod->id;
    $a["protocol"] = "http";
    if (strpos($lastUrl,"ftp://")===0)
      $a["protocol"] = "ftp";
    $a["testDate"] = date("Y-m-d H:i:s");
    $a["returnCode"] = $sideload->httpReturnCode;
    $a["returnContentType"] = $sideload->httpReturnContentType;

    SQLLib::UpdateOrInsertRow("prods_linkcheck",$a,sprintf_esc("prodID=%d",$prod->id));
    
    if ($id)
    {
      $out[] = json_encode($a);
      $out[] = "\n[".$prod->id."] " . json_encode($urls) . " >> ". $a["returnCode"];
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
