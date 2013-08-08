<?
include_once("bootstrap.inc.php");

$r = SQLLib::selectRows(sprintf_esc("select name,type,party_year from prods where name like '%%%s%%' order by views desc limit 10",_like($_GET["search"])));
$res[0] = $_GET["what"];
foreach($r as $o)
{
  $res[1][] = $o->name;
  $res[2][] = $o->type.($o->party_year?", ".$o->party_year:"");
}   
echo json_encode($res);
?>