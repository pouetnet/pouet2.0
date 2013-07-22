<?
include_once("bootstrap.inc.php");

header("Content-type: application/json; charset=utf-8");

$sql = "";
if ($_POST["search"])
  $sql = sprintf_esc("select id, name from groups where name like '%%%s%%' or acronym like '%%%s%%' order by name limit 10",addcslashes($_POST["search"],"%_"),addcslashes($_POST["search"],"%_"));
else if ($_POST["id"])
  $sql = sprintf_esc("select id, name from groups where id = %d limit 1",$_POST["id"]);

if ($sql)
  $r = SQLLib::selectRows($sql);

echo json_encode($r);
?>