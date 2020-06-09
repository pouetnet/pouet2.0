<?php
require_once("bootstrap.inc.php");

header("Content-type: application/json; charset=utf-8");

$sql = new SQLSelect();
$sql->AddTable("bbs_posts");
$sql->AddField("bbs_topics.topic as topic");
$sql->AddField("bbs_topics.id as topicID");
$sql->AddField("bbs_posts.id as id");
if ($_POST["search"]) $sql->AddField("'".$_POST["search"]."' as searchQuery");
$sql->AddField("bbs_posts.post as post");
$sql->AddField("bbs_posts.added as postDate");
$sql->AddJoin("left","bbs_topics","bbs_posts.topic = bbs_topics.id");
  
$r = array();
if ($_POST["search"])
{
  $sql->AddWhere(sprintf_esc("(bbs_posts.post LIKE '%%%s%%' or bbs_topics.topic LIKE '%%%s%%')",_like($_POST["search"]),_like($_POST["search"])));
  $sql->AddOrder("bbs_posts.added DESC");
  $sql->SetLimit(5);
  $r = SQLLib::selectRows( $sql->GetQuery() );
}
else if ($_POST["id"])
{
  $sql->AddWhere(sprintf_esc("id = %d",$_POST["id"]));
  $sql->SetLimit(1);
  $r = SQLLib::selectRows( $sql->GetQuery() );
}

echo json_encode($r);
?>
