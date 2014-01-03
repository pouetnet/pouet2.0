<?
require_once("../bootstrap.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/box-index-latestadded.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$limit = $_GET["howmany"] ? $_GET["howmany"] : 10;
$limit = min($limit,25);
$limit = max($limit,5);

$s = new BM_Query("prods");
$s->AddOrder("prods.quand DESC");
$s->attach("added",array("users as user"=>"id"));
$s->SetLimit($limit);

if ($_GET["type"])
{
  $s->AddWhere(sprintf_esc("FIND_IN_SET('%s',prods.type)",$_GET["type"]));
}
if ($_GET["platform"])
{
  $platformID = -1;
  foreach($PLATFORMS as $k=>$v)
    if ($v["name"] == $_GET["platform"])
      $platformID = $k;
  if ($platformID != -1)
  {
    $s->AddJoin("LEFT","prods_platforms as pp","pp.prod = prods.id");
    $s->AddWhere(sprintf_esc("pp.platform = %d",$platformID));
  }
}

$data = $s->perform();
PouetCollectPlatforms($data);

$rss = new PouetRSS();

foreach($data as $item)
{
  $rss->AddItem(array(
    "title"     => $item->name,
    "link"      => POUET_ROOT_URL . "prod.php?which=" . $item->id,
    "pubDate"   => date("r",strtotime($item->quand)),
    "enclosure" => find_screenshot($item->id),
  ));
}

$rss->Render();

?>
