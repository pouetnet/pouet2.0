<?
include_once("bootstrap.inc.php");

header("Content-type: application/json; charset=utf-8");

$sql = "";
if ($_POST["search"])
  $sql = sprintf_esc("select id, name from parties where name like '%%%s%%' order by name limit 10",_like($_POST["search"]),_like($_POST["search"]));
else if ($_POST["id"])
  $sql = sprintf_esc("select id, name from parties where id = %d limit 1",$_POST["id"]);

if ($sql)
  $r = SQLLib::selectRows($sql);

echo json_encode($r);
?>