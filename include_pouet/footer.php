<footer>

<a href="index.php">pouët.net</a> 2.0 &copy; 2000-<?=date("Y")?> <a href="groups.php?which=5">mandarine</a>
- hosted on <a href="http://www.scene.org/">scene.org</a>
- follow us on <a href="https://twitter.com/pouetdotnet">twitter</a><br />

send comments and bug reports to <a href="mailto:webmaster@pouet.net">webmaster@pouet.net</a>
or <a href="https://github.com/pouetnet/pouet2.0/">github</a><br />
<?
$timer["html"]["end"] = microtime_float();
$timer["page"]["end"] = microtime_float();
printf("page created in %f seconds with %d queries.<br/>\n",$timer["page"]["end"] - $timer["page"]["start"],count($SQLLIB_QUERIES));

if (POUET_TEST)
{
  $data = @file_get_contents( POUET_ROOT_LOCAL . "/.git/HEAD");
  if (preg_match("/ref: refs\/heads\/(.*)/",$data,$m))
  {
    printf("current development branch: %s.<br/>\n",$m[1]);
  }
}
echo "</footer>";

if (POUET_TEST)
{
  foreach($timer as $k=>$v) {
    printf("<!-- %-40s took %f -->\n",$k,$v["end"] - $v["start"]);
  }
  echo "<!--\n";
  echo "QUERIES:\n";
  $n=1;
  foreach($SQLLIB_QUERIES as $sql=>$time)
    printf("%3d. [%8.2f] - %s\n",$n++,$time,_html($sql));
  echo "-->";
}
require_once("footer.bare.php");
?>
