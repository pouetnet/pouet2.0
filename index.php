<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-login.php");
require_once("include_pouet/box-index-bbs-latest.php");
require_once("include_pouet/box-index-cdc.php");
require_once("include_pouet/box-index-watchlist.php");
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
require("include_pouet/menu.inc.php");

$customizerJSON = get_setting("customizerJSON");
$customizer = json_decode($customizerJSON,true);
if (!$customizer["frontpage"])
{
  require_once("include_pouet/default_usersettings.php");
  $customizer = json_decode($DEFAULT_USERSETTINGS->customizerJSON, true);
}
$boxes = $customizer["frontpage"];

echo "<div id='content' class='frontpage'>\n";

foreach($boxes as $bar=>$boxlist)
{
  $timer["bar_".$bar]["start"] = microtime_float();
  echo "  <div id='"._html($bar)."' class='column'>\n";
  foreach($boxlist as $box)
  {
    $class = "PouetBoxIndex".$box["box"];
    if (!class_exists($class))
      continue;
    $p = new $class();
    
    if (!$currentUser && !$p->IsVisibleLoggedOut())
      continue;
    if (has_trait($p,"PouetFrontPage"))
      $p->SetParameters($box);
    $p->Load(true);
    $p->Render();
  }
  echo "  </div>\n";
  $timer["bar_".$bar]["end"] = microtime_float();
}

echo "</div>\n";

?>
<script type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  if (Pouet.isMobile)
  {
    CollapsibleHeaders( $$(".pouettbl") );
  }
});
//-->
</script>
<?
require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
