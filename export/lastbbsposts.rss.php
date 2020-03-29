<?php
require_once("../bootstrap.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet_index/box-index-bbs-latest.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$limit = $_GET["howmany"] ? $_GET["howmany"] : 10;
$limit = min($limit,25);
$limit = max($limit,5);

$p = new PouetBoxIndexLatestBBS();
$p->Load(true);

$rss = new PouetRSS();

$n = 1;
foreach($p->data as $item)
{
  $rss->AddItem(array(
    "title"     => $item->topic,
    "link"      => POUET_ROOT_URL . "topic.php?which=" . $item->id,
    "pubDate"   => date("r",strtotime($item->lastpost)),
  ));
  if ($n++ >= $limit) break;
}

$rss->Render();

?>
