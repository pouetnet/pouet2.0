<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-login.php");
include_once("include_pouet/box-index-bbs-latest.php");
include_once("include_pouet/box-index-cdc.php");
include_once("include_pouet/box-index-latestadded.php");
include_once("include_pouet/box-index-latestreleased.php");
include_once("include_pouet/box-index-latestcomments.php");
include_once("include_pouet/box-index-latestparties.php");
include_once("include_pouet/box-index-upcomingparties.php");
include_once("include_pouet/box-index-topmonth.php");
include_once("include_pouet/box-index-topalltime.php");
include_once("include_pouet/box-index-news.php");
include_once("include_pouet/box-index-searchbox.php");
include_once("include_pouet/box-index-affilbutton.php");
include_once("include_pouet/box-index-stats.php");
include_once("include_pouet/box-index-user-topglops.php");
include_once("include_pouet/box-index-oneliner-latest.php");

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

// the way it's done like this is so that later
// we can allow the user to customize/reorder/etc it.
$boxes = array(
  "leftbar" => array(
    array("box"=>"Login"),
    array("box"=>"CDC"),
    array("box"=>"LatestAdded"),
    array("box"=>"LatestReleased"),
    array("box"=>"TopMonth"),
    array("box"=>"TopAlltime"),
  ),
  "middlebar" => array(
    array("box"=>"LatestOneliner"),
    array("box"=>"LatestBBS"),
    array("box"=>"NewsBoxes"),
  ),
  "rightbar" => array(
    array("box"=>"SearchBox"),
    array("box"=>"Stats"),
    array("box"=>"AffilButton"),
    array("box"=>"LatestComments"),
    array("box"=>"LatestParties"),
    array("box"=>"UpcomingParties"),
    array("box"=>"TopGlops"), 
  ),
);

echo "<div id='content' class='frontpage'>\n";

foreach($boxes as $bar=>$boxlist)
{
  $st = microtime_float();
  echo "  <div id='"._html($bar)."'>\n";
  foreach($boxlist as $box)
  {
    $class = "PouetBox".$box["box"];
    $p = new $class();
    $p->Load(true);
    $p->Render();
  }
  echo "  </div>\n";
  printf("<!-- presentation of %s took %f-->\n",$bar,microtime_float() - $st);
}

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>
