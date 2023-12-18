<?php
require_once("../bootstrap.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet_index/box-index-latestadded.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$limit = @$_GET["howmany"] ? (int)$_GET["howmany"] : 10;
$limit = min($limit,25);
$limit = max($limit,5);

$s = new BM_Query("prods");
$s->AddOrder("prods.addedDate DESC");
$s->attach("addedUser",array("users as user"=>"id"));
$s->SetLimit($limit);

if (@$_GET["type"])
{
  $s->AddWhere(sprintf_esc("FIND_IN_SET('%s',prods.type)",$_GET["type"]));
}
if (@$_GET["platform"])
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
    "title"       => $item->name . ($item->groups ? " by ".$item->RenderGroupsPlain() : ""),
    "pouet:title" => $item->name,
    "pouet:group" => array_map(function($i){ return $i->name; },$item->groups),
    "pouet:party" => array_map(function($i){ return trim($i->party->name." ".$i->year); },$item->placings),
    "pouet:type" => explode(",",$item->type),
    "pouet:platform" => array_map(function($i){ return $i["name"]; },$item->platforms),
    "link"      => POUET_ROOT_URL . "prod.php?which=" . $item->id,
    "pubDate"   => date("r",strtotime($item->addedDate)),
    "enclosure" => find_screenshot($item->id),
  ));
}

$rss->Render();

?>
