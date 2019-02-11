<?
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

?>