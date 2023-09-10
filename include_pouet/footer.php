<footer>

<ul>
  <li><a href="//<?=(POUET_MOBILE?POUET_WEB_HOSTNAME:POUET_MOBILE_HOSTNAME).$_SERVER["REQUEST_URI"]?>">switch to <?=(POUET_MOBILE?"desktop":"mobile")?> version</a></li>
  <li>
    <a href="index.php">pouët.net</a> 2.0 &copy; 2000-<?=date("Y")?> <a href="groups.php?which=5">mandarine</a>
    - hosted on <a href="http://www.scene.org/">scene.org</a>
    - follow us on <a href="https://twitter.com/pouetdotnet">twitter</a> and <a href="https://www.facebook.com/pouet.dot.net">facebook</a>
    - join us on <a href="https://discord.gg/MCDXrrB">discord</a> and <a href="https://webchat.ircnet.net/?channels=%23pouet.net&uio=OT10cnVlde">irc</a>
  </li>
  <li>
    send comments and bug reports to <a href="mailto:webmaster@pouet.net">webmaster@pouet.net</a>
    or <a href="https://github.com/pouetnet/pouet2.0/">github</a>
  </li>
<?php
$timer["html"]["end"] = microtime_float();
$timer["page"]["end"] = microtime_float();
if (SQLLib::$telemetry)
{
  printf("<li>page created in %f seconds with %d queries.</li>\n",$timer["page"]["end"] - $timer["page"]["start"],count(SQLLib::$queries));
}
else
{
  printf("<li>page created in %f seconds.</li>\n",$timer["page"]["end"] - $timer["page"]["start"]);
}

if (POUET_TEST)
{
  $data = @file_get_contents( POUET_ROOT_LOCAL . "/.git/HEAD");
  if (preg_match("/ref: refs\/heads\/(.*)/",$data,$m))
  {
    printf("<li>current development branch: %s.</li>\n",$m[1]);
  }
}
echo "</ul>\n";
echo "</footer>";

if (POUET_TEST)
{
  foreach($timer as $k=>$v) {
    printf("<!-- %-40s took %f -->\n",$k,$v["end"] - $v["start"]);
  }
  echo "<!--\n";
  echo "QUERIES:\n";
  $n=1;
  foreach(SQLLib::$queries as $sql=>$time)
    printf("%3d. [%8.2f] - %s\n",$n++,$time,_html($sql));
  echo "-->";
}
require_once("footer.bare.php");
?>
