<?php
require_once("bootstrap.inc.php");

header("Content-type: application/json; charset=utf-8");

$sql = new SQLSelect();
$sql->AddField("id");
$sql->AddField("name");
$sql->AddField("disambiguation");
$sql->AddTable("groups");

$r = array();
if (@$_POST["search"])
{
  $terms = split_search_terms( $_POST["search"] );
  foreach($terms as $term)
  {
    $sql->AddWhere(sprintf_esc("name like '%%%s%%' or acronym like '%%%s%%'",_like($term),_like($term)));
  }
  $sql->AddOrder(sprintf_esc("if(name='%s' or acronym='%s',1,2), name",$_POST["search"],$_POST["search"]));
  $sql->SetLimit(10);
  $r = SQLLib::selectRows( $sql->GetQuery() );
}
else if (@$_POST["id"])
{
  $sql->AddWhere(sprintf_esc("id = %d",$_POST["id"]));
  $sql->SetLimit(1);
  $r = SQLLib::selectRows( $sql->GetQuery() );
}

echo json_encode($r);
?>
