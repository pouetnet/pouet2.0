<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-login.php");

require_once("include_pouet_index/index_bootstrap.inc.php");

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

$customizerJSON = get_setting("customizerJSON");
$customizer = json_decode($customizerJSON,true);
if (!$customizer || !$customizer["frontpage"])
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
    {
      continue;
    }
    $p = new $class();
    
    if (!$currentUser && !$p->IsVisibleLoggedOut())
    {
      continue;
    }
    if (has_trait($p,"PouetFrontPage"))
    {
      $p->SetParameters($box);
    }
    $p->Load(true);
    $p->Render();
  }
  echo "  </div>\n";
  $timer["bar_".$bar]["end"] = microtime_float();
}

echo "</div>\n";

?>
<script>
<!--
document.observe("dom:loaded",function(){
  if (Pouet.isMobile)
  {
    CollapsibleHeaders( $$(".pouettbl") );
  }
});
//-->
</script>
<?php
require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
