<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-login.php");
require_once("include_pouet/box-index-bbs-latest.php");
require_once("include_pouet/box-index-cdc.php");
require_once("include_pouet/box-index-latestadded.php");
require_once("include_pouet/box-index-latestreleased.php");
require_once("include_pouet/box-index-latestcomments.php");
require_once("include_pouet/box-index-latestparties.php");
require_once("include_pouet/box-index-upcomingparties.php");
require_once("include_pouet/box-index-topmonth.php");
require_once("include_pouet/box-index-topalltime.php");
require_once("include_pouet/box-index-news.php");
require_once("include_pouet/box-index-searchbox.php");
require_once("include_pouet/box-index-affilbutton.php");
require_once("include_pouet/box-index-stats.php");
require_once("include_pouet/box-index-user-topglops.php");
require_once("include_pouet/box-index-oneliner-latest.php");

require_once("include_pouet/header.php");
require_once("include_pouet/menu.inc.php");

// the reason this is done like this is so that later
// we can allow the user to customize/reorder/etc it.
$boxes = array(
  "leftbar" => array(
    array("box"=>"Login"),
    array("box"=>"CDC"           ,"limit"=>get_setting("indexcdc")),
    array("box"=>"LatestAdded"   ,"limit"=>get_setting("indexlatestadded")),
    array("box"=>"LatestReleased","limit"=>get_setting("indexlatestreleased")),
    array("box"=>"TopMonth"      ,"limit"=>get_setting("indextopprods")),
    array("box"=>"TopAlltime"    ,"limit"=>get_setting("indextopkeops")),
  ),
  "middlebar" => array(
    array("box"=>"LatestOneliner","limit"=>get_setting("indexoneliner")),
    array("box"=>"LatestBBS"     ,"limit"=>get_setting("indexbbstopics")),
    array("box"=>"NewsBoxes"     ,"limit"=>get_setting("indexojnews")),
  ),
  "rightbar" => array(
    array("box"=>"SearchBox"      ,"limit"=>get_setting("indexsearch")),
    array("box"=>"Stats"          ,"limit"=>get_setting("indexstats")),
    array("box"=>"AffilButton"    ,"limit"=>get_setting("indexlinks")),
    array("box"=>"LatestComments" ,"limit"=>get_setting("indexlatestcomments")),
    array("box"=>"LatestParties"  ,"limit"=>get_setting("indexlatestparties")),
    array("box"=>"UpcomingParties"),
    array("box"=>"TopGlops"       ,"limit"=>get_setting("indextopglops")), 
  ),
);

echo "<div id='content' class='frontpage'>\n";

foreach($boxes as $bar=>$boxlist)
{
  $st = microtime_float();
  echo "  <div id='"._html($bar)."'>\n";
  foreach($boxlist as $box)
  {
    if ($box["limit"]===0)
      continue;
    $class = "PouetBox".$box["box"];
    $p = new $class();
    $p->SetParameters($box);
    $p->Load(true);
    $p->Render();
  }
  echo "  </div>\n";
  printf("<!-- presentation of %s took %f-->\n",$bar,microtime_float() - $st);
}

echo "</div>\n";

require_once("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
