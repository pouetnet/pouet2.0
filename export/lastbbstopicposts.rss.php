<?php
require_once("../bootstrap.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet_index/box-index-bbs-latest.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-rss.php");

$limit = 25;

$s = new SQLSelect();
$s->AddTable("bbs_topics");
$s->AddWhere("bbs_topics.id=".(int)@$_GET["topic"]);
$topic = SQLLib::SelectRow($s->GetQuery());
if(!$topic) exit();

$s = new BM_Query();
$s->AddTable("bbs_posts");
$s->AddField("bbs_posts.id as id");
$s->AddField("bbs_posts.post as post");
$s->AddField("bbs_posts.added as added");
$s->attach(array("bbs_posts"=>"author"),array("users as user"=>"id"));
$s->AddWhere("bbs_posts.topic=".$topic->id);
$s->AddOrder("bbs_posts.id desc");
$s->SetLimit( 25 );

$data = $s->perform();

$rss = new PouetRSS(array(
  "title"=>"pouët.net bbs - ".$topic->topic,
  "link"=>POUET_ROOT_URL . "topic.php?which=" . $topic->id,
));

$n = 1;
foreach($data as $item)
{
  $rss->AddItem(array(
    "title"     => shortify($item->post),
    "description" => $item->post,
    "link"      => POUET_ROOT_URL . "topic.php?post=" . $item->id,
    "pubDate"   => date("r",strtotime($item->added)),
  ));
}

$rss->Render();

?>
