<?php
require_once("bootstrap.inc.php");

header("Content-type: application/json; charset=utf-8");

$sql = new SQLSelect();
$sql->AddField("count(*) as avatarCount");
$sql->AddTable("users");
$sql->AddWhere(sprintf_esc("avatar = '%s'",$_POST["avatar"]));
if ($currentUser)
{
  $sql->AddWhere(sprintf_esc("id != %d",$currentUser->id));
}
$r = SQLLib::selectRow( $sql->GetQuery() );
echo json_encode($r);
?>
