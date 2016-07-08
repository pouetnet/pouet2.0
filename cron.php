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
    $s->AddWhere("prods_linkcheck.testDate is NULL or datediff(now(),prods_linkcheck.testDate) > 30");
  $s->SetLimit( 20 );
  $prods = SQLLib::SelectRows( $s->GetQuery() );
  $out = array();
  foreach($prods as $prod)
  {
    $ch = curl_init();
    $urls = array();
    $url = verysofturlencode($prod->download);
    for ($x=0; $x<10; $x++)
    {
      curl_setopt($ch, CURLOPT_URL, $url);
      //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_USERAGENT, "Pouet-BrokenLinkCheck/2.0");
      //curl_setopt($ch, CURLOPT_NOBODY, true);
      $dataLength = 0;
      curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch,$data)use($dataLength){
        $length = strlen($data);
        $dataLength += $length;
        if ($dataLength > 1024) // abort download after 1k
          return 0;
        return $length;
      });
      curl_setopt($ch, CURLOPT_HTTPGET, true);    
      
      $urls[] = $url;
      curl_exec($ch);
      
      $lastUrl = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
      if ($lastUrl == $url)
        break;
      $url = $lastUrl;
    }

    $a = array();
    $a["prodID"] = $prod->id;
    $a["protocol"] = "http";
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
