<?php
if(php_sapi_name() != "cli")
  die("commandline only!");

// change to pouet root
chdir( dirname( __FILE__ ) );

require_once("bootstrap.inc.php");
require_once("admin.functions.php");

echo "[".date("Y-m-d H:i:s")."] Cron running: ".$argv[1]."\n";

function cron_CheckLinks( $ids = null )
{
  $s = new SQLSelect();
  $s->AddField("prods.id");
  $s->AddField("prods.download");
  $s->AddTable("prods");
  $s->AddJoin("left","prods_linkcheck","prods_linkcheck.prodID = prods.id");
  $s->AddOrder("prods_linkcheck.testDate");
  if ($ids)
  {
    $s->AddWhere(sprintf("prods.id in (%s)",implode(",",array_map(function($i){ return (int)$i; },$ids))));
  }
  else
  {
    $s->AddWhere("prods_linkcheck.testDate is NULL or datediff(now(),prods_linkcheck.testDate) > 20");
    $s->SetLimit( 20 );
    $s->AddOrder("RAND()");
  }
  $prods = SQLLib::SelectRows( $s->GetQuery() );
  $out = array();
  foreach($prods as $prod)
  {
    $out[] = pouetAdmin_recheckLinkProd($prod);
    usleep(500000);
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
    $content = cron_CheckLinks( array_slice($argv,2) );
    echo " > linkCheck: ".$content."\n";
    break;
  case "createDataDump":
    $content = pouetAdmin_createDataDump();
    echo " > dataDump: ".$content."\n";
    break;
}

echo "[".date("Y-m-d H:i:s")."] Cron finished.\n\n";
?>
