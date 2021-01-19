<?php
// recache kelemen

function pouetAdmin_recacheFrontPage()
{
  $content = "<ul>";
  foreach(glob("cache/*") as $v) { $content .= "<li>deleting '".$v."'</li>\n"; @unlink($v); }
  $content .= "</ul>";
  return $content;
}
function pouetAdmin_recacheFrontPagePartial()
{
  $content = "<ul>";
  foreach(glob("cache/*") as $v) if ($_POST["deleteCache"][basename($v)] == "on") { $content .= "<li>deleting '".$v."'</li>\n"; @unlink($v); }
  $content .= "</ul>";
  return $content;
}

function pouetAdmin_recacheTopDemos()
{
  global $timer;
  
  // this needs to be made faster. a LOT faster.
  $total = array();

  // list by views
  $timer["recache_views"]["start"] = microtime_float();
  $i=0;
  $query="SELECT id,name,views FROM prods ORDER BY views DESC";
  $result = SQLLib::Query($query);
  $content = "<ol>";
  while($tmp = SQLLib::Fetch($result)) {
    $total[$tmp->id]+=$i;
    $i++;
    if ($i<=5)
      $content .= "<li><b>"._html($tmp->name)."</b> - ".$tmp->views." views</li>\n";
  }
  $content .= "</ol>";
  $content .= "<h3>".$i." prod views loaded</h3>\n";
  $timer["recache_views"]["end"] = microtime_float();

  $i=0;
  // Get the list of prod IDs ordered by the sum of their comment ratings
  $sql = new SQLSelect();
  $sql->AddField("prods.id");
  $sql->AddField("prods.name");
  $sql->AddField("SUM(comments.rating) as theSum");
  $sql->AddTable("prods");
  $sql->AddJoin("","comments","prods.id = comments.which");
  $sql->AddGroup("prods.id");
  $sql->AddOrder("SUM(comments.rating) DESC");

  $timer["recache_votes"]["start"] = microtime_float();
  $result = SQLLib::Query( $sql->GetQuery() );
  $content .= "<ol>";
  while($tmp = SQLLib::Fetch($result)) {
    $total[$tmp->id]+=$i;
    $i++;
    if ($i<=5)
      $content .= "<li><b>"._html($tmp->name)."</b> - "._html($tmp->theSum)." votes</li>\n";
  }
  $content .= "</ol>";
  $content .= "<h3>".$i." vote counts loaded</h3>\n";
  $timer["recache_votes"]["end"] = microtime_float();

  $timer["recache_sort"]["start"] = microtime_float();
  asort($total);
  $timer["recache_sort"]["end"] = microtime_float();

  $timer["recache_update"]["start"] = microtime_float();
  $i=1;
  unset($tmp);
  unset($top_demos);
  $a = array();
  foreach($total as $key=>$val)
  {
    $a[] = array(
      "id" => $key,
      "rank" => $i,
    );
    if (count($a) == 100)
    {
      SQLLib::UpdateRowMulti("prods","id",$a);
      $a = array();
    }
    $i++;
  }
  SQLLib::UpdateRowMulti("prods","id",$a);
  $content .= "<h3>".$i." prod rankings updated</h3>\n";
  $timer["recache_update"]["end"] = microtime_float();

  @unlink('cache/pouetbox_topalltime.cache');
  @unlink('cache/pouetbox_topmonth.cache');
  return $content;
}

function pouetAdmin_recheckLinkProd($prod)
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
    return "";
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
    $out .= json_encode($a);
    $out .= "\n[".$prod->id."] " . json_encode($urls) . " >> ". $a["returnCode"];
  }
  else
  {
    $out = $prod->id . " -> " . $a["returnCode"];
  }
  return $out;
}
function pouetAdmin_recheckLink($id)
{
  $prod = PouetProd::Spawn($id);
  return pouetAdmin_recheckLinkProd($prod);
}
function pouetAdmin_createDataDump()
{
  if (!defined("POUET_DATADUMP_PATH")) return;
  
  $dateStamp = date("Y-m-d H:i:s");

  // prods
  $filename = "pouetdatadump-prods-" . substr(preg_replace("/[^0-9]+/","",$dateStamp),0,8) . ".json.gz";
  $gz = gzopen(POUET_DATADUMP_PATH . $filename . ".inprogress",'w9');
  gzwrite($gz, '{"dump_date":"'.$dateStamp.'","prods":[');
  $first = true;
  $rows = SQLLib::SelectRows("select id from prods order by id");
  foreach($rows as $row)
  {
    if (!$first) gzwrite($gz, ",");
    $first = false;
    $item = PouetProd::Spawn($row->id);
    $a = array(&$item);
    
    PouetCollectPlatforms( $a );
    PouetCollectAwards( $a );
  
    $item->downloadLinks = SQLLib::selectRows(sprintf_esc("select type, link from downloadlinks where prod = %d order by type",$item->id));
    
    $s = new BM_Query("credits");
    $s->AddField("credits.role");
    $s->AddWhere(sprintf("credits.prodID = %d",$item->id));
    $s->Attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddOrder("credits.role");
    $item->credits = $s->perform();
    
    gzwrite($gz, json_encode($item->ToAPI()) );
  }
  gzwrite($gz, ']}');
  gzclose($gz);
  rename(POUET_DATADUMP_PATH . $filename . ".inprogress", POUET_DATADUMP_PATH . $filename);
  $out[] = sprintf("dumped %d prods into %s",count($rows),$filename);
  
  // groups
  $filename = "pouetdatadump-groups-" . substr(preg_replace("/[^0-9]+/","",$dateStamp),0,8) . ".json.gz";
  $gz = gzopen(POUET_DATADUMP_PATH . $filename . ".inprogress",'w9');
  gzwrite($gz, '{"dump_date":"'.$dateStamp.'","groups":[');
  $first = true;
  $rows = SQLLib::SelectRows("select id from groups order by id");
  foreach($rows as $row)
  {
    if (!$first) gzwrite($gz, ",");
    $first = false;
    $item = PouetGroup::Spawn($row->id);
    gzwrite($gz, json_encode($item->ToAPI()) );
  }
  gzwrite($gz, ']}');
  gzclose($gz);
  rename(POUET_DATADUMP_PATH . $filename . ".inprogress", POUET_DATADUMP_PATH . $filename);
  $out[] = sprintf("dumped %d groups into %s",count($rows),$filename);

  // parties
  $filename = "pouetdatadump-parties-" . substr(preg_replace("/[^0-9]+/","",$dateStamp),0,8) . ".json.gz";
  $gz = gzopen(POUET_DATADUMP_PATH . $filename . ".inprogress",'w9');
  gzwrite($gz, '{"dump_date":"'.$dateStamp.'","parties":[');
  $first = true;
  $rows = SQLLib::SelectRows("select id from parties order by id");
  foreach($rows as $row)
  {
    if (!$first) gzwrite($gz, ",");
    $first = false;
    $item = PouetParty::Spawn($row->id);
    gzwrite($gz, json_encode($item->ToAPI()) );
  }
  gzwrite($gz, ']}');
  gzclose($gz);
  rename(POUET_DATADUMP_PATH . $filename . ".inprogress", POUET_DATADUMP_PATH . $filename);
  $out[] = sprintf("dumped %d groups into %s",count($rows),$filename);
  
  // boards
  $filename = "pouetdatadump-boards-" . substr(preg_replace("/[^0-9]+/","",$dateStamp),0,8) . ".json.gz";
  $gz = gzopen(POUET_DATADUMP_PATH . $filename . ".inprogress",'w9');
  gzwrite($gz, '{"dump_date":"'.$dateStamp.'","boards":[');
  $first = true;
  $rows = SQLLib::SelectRows("select id from boards order by id");
  foreach($rows as $row)
  {
    if (!$first) gzwrite($gz, ",");
    $first = false;
    $item = PouetBoard::Spawn($row->id);
    gzwrite($gz, json_encode($item->ToAPI()) );
  }
  gzwrite($gz, ']}');
  gzclose($gz);
  rename(POUET_DATADUMP_PATH . $filename . ".inprogress", POUET_DATADUMP_PATH . $filename);
  $out[] = sprintf("dumped %d groups into %s",count($rows),$filename);
  
  return implode("\n",$out);
}
function pouetAdmin_recacheBBS()
{
  $topics = SQLLib::SelectRows("SELECT id FROM bbs_topics ORDER BY lastpost DESC LIMIT 20");

  $content = "Updated ";
  foreach($topics as $topic)
  {
    $post = SQLLib::SelectRow("SELECT * FROM bbs_posts WHERE topic=".(int)$topic->id." ORDER BY added DESC");

  	$a = array();
  	$a["userlastpost"] = $post->author;
  	$a["lastpost"] = $post->added;
  	
  	$count = SQLLib::SelectRow("SELECT count(*) AS c FROM bbs_posts WHERE topic=".(int)$topic->id."");
  	$a["count"] = $count->c - 1;
  	
  	$content .= $topic->id.", ";

    SQLLib::UpdateRow("bbs_topics",$a,"id=".$topic->id);
  }
  
  @unlink('cache/pouetbox_latestbbs.cache');

  return $content;
}

?>