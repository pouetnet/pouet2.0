<?
require_once("../bootstrap.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$s = new BM_Query("comments");
$s->AddField("comments.id");
$s->AddField("comments.quand");
$s->AddField("comments.comment");
$s->attach(array("comments"=>"which"),array("prods as prod"=>"id"));        
$s->attach(array("comments"=>"who"),array("users as user"=>"id"));        
$s->AddWhere(sprintf_esc("comments.which = %d",$_GET["prod"]));
$s->AddOrder("comments.quand DESC");
$s->SetLimit("20");
$data = $s->perform();

$rss = new PouetRSS();

foreach($data as $item)
{
  $rss->AddItem(array(
    "title"     => $item->user->nickname,
    "description" => $item->comment,
    "link"      => POUET_ROOT_URL . "prod.php?which=" . $item->prod->id,
    "pubDate"   => date("r",strtotime($item->quand)),
    "guid"      => $item->id,
  ));
}

$rss->Render();

?>
