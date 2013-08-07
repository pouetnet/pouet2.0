<?
include_once("../bootstrap.inc.php");
include_once( POUET_ROOT_LOCAL . "/include_pouet/box-index-bbs-latest.php");
include_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$p = new PouetBoxLatestBBS();
$p->Load(true);

$rss = new PouetRSS();

foreach($p->data as $item)
{
  $rss->AddItem(array(
    "title"     => $item->topic,
    "link"      => POUET_ROOT_URL . "topic.php?which=" . $item->id,
    "pubDate"   => date("r",strtotime($item->lastpost)),
  ));
}

$rss->Render();

?>
