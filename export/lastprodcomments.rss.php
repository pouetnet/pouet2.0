<?php
require_once("../bootstrap.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$limit = @$_GET["howmany"] ? (int)$_GET["howmany"] : 10;
$limit = min($limit,25);
$limit = max($limit,5);

$prod = PouetProd::Spawn( @$_GET["prod"] );
if (!$prod) exit();

$s = new BM_Query();
$s->AddTable("comments");
$s->AddField("comments.id");
$s->AddField("comments.addedDate");
$s->AddField("comments.comment");
$s->AddField("comments.rating");
$s->attach(array("comments"=>"who"),array("users as user"=>"id"));
$s->AddWhere(sprintf_esc("comments.which = %d",$_GET["prod"]));
$s->AddOrder("comments.addedDate DESC");
$s->SetLimit($limit);
$data = $s->perform();

$rss = new PouetRSS(array(
  "title"=>"pouët.net - prod comments for ".$prod->name,
  "link"=>$prod->GetLink(),
));

$votes = array( -1 => "sucks", 0 => "isok", 1 => "rulez" );
foreach($data as $item)
{
  $rss->AddItem(array(
    "title"     => $item->user->nickname,
    "description" => $item->comment,
    "link"      => POUET_ROOT_URL . "prod.php?post=" . $item->id,
    "pubDate"   => date("r",strtotime($item->addedDate)),
    "guid"      => "pouetcomment".$item->id,
    "pouet:vote" => $votes[ $item->rating ],
  ));
}

$rss->Render();

?>
