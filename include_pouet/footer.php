<footer>
<a href="index.php">pouët.net</a> 2.0 &copy; 2000-<?=date("Y")?> <a href="groups.php?which=5">mandarine</a> and <a href="masthead.php">some others</a> - hosted on <a href="http://www.scene.org/">scene.org</a><br />
send comments and bug reports to <a href="mailto:webmaster@pouet.net">webmaster@pouet.net</a><br />
<?
$timer["html"]["end"] = microtime_float();
$timer["page"]["end"] = microtime_float();
printf("page created in %f seconds with %d queries.\n",$timer["page"]["end"] - $timer["page"]["start"],count($SQLLIB_QUERIES));
?>
</footer>
<?
if (POUET_TEST)
{
  foreach($timer as $k=>$v) {
    printf("<!-- %-40s took %f -->\n",$k,$v["end"] - $v["start"]);
  }
//  echo "<!--\nQUERIES:\n".print_r($SQLLIB_QUERIES,true)."\n-->";
}
include_once("footer.bare.php");
?>
