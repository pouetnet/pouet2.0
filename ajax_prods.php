<?php
require_once("bootstrap.inc.php");

header("Content-type: application/json; charset=utf-8");

$sql = new SQLSelect();
$sql->AddField("prods.id");
$sql->AddField("prods.name");
$sql->AddField("groups.name as groupName");
$sql->AddJoin("left","groups","groups.id = prods.group1");
$sql->AddTable("prods");

$r = array();
if ($_POST["search"])
{
  $terms = split_search_terms( $_POST["search"] );
  foreach($terms as $term)
  {
    $sql->AddWhere(sprintf_esc("prods.name like '%%%s%%'",_like($term)));
  }
  $sql->AddOrder(sprintf_esc("if(prods.name='%s',1,2), prods.views desc, prods.name",$_POST["search"]));
  $sql->SetLimit(10);
  $r = SQLLib::selectRows( $sql->GetQuery() );
}
else if ($_POST["id"])
{
  $sql->AddWhere(sprintf_esc("prods.id = %d",$_POST["id"]));
  $sql->SetLimit(1);
  $r = SQLLib::selectRows( $sql->GetQuery() );
}
echo json_encode($r);
?>
