<?
require_once("../bootstrap.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/box-index-latestadded.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$p = new PouetBoxLatestAdded();
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
