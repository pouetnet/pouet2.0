<?
include_once("../bootstrap.inc.php");
include_once( POUET_ROOT_LOCAL . "/include_pouet/box-index-latestreleased.php");
include_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$p = new PouetBoxLatestReleased();
$p->Load(true);

$rss = new PouetRSS();

foreach($p->data as $item)
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
