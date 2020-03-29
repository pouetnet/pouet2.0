<?php
require_once("bootstrap.inc.php");

header("Content-type: application/json; charset=utf-8");

$sql = new SQLSelect();
$sql->AddField("party_compo");
$sql->AddTable("prods");
$sql->AddGroup("party_compo");
$sql->AddWhere(sprintf_esc("party=%d",$_GET["party"]));
$sql->AddWhere(sprintf_esc("party_year=%d",$_GET["year"]));

$r = SQLLib::selectRows( $sql->GetQuery() );

echo json_encode( array("compos"=>array_map(function($i){ return (int)$i->party_compo; },$r)) );
?>
