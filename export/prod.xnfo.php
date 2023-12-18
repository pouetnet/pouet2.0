<?php
require_once("../bootstrap.inc.php");

header("Content-type: application/xml; charset=utf-8");
//header("Content-type: text/plain; charset=utf-8");

$xml = new SimpleXMLElement("<"."?xml version='1.0' encoding='UTF-8'?"."><xnfo/>");

$prod = PouetProd::Spawn( @$_GET["which"] );

if (!$prod)
  die($xml->AsXML());

$a = array(&$prod);
PouetCollectPlatforms( $a );

$xml->addAttribute("standard","1.1");
$xml->addAttribute("version","1");
$xml->addAttribute("author","webmaster@pouet.net");
$xml->addAttribute("mode","partial");

$xml->addChild("demo");
$xml->demo->addAttribute("pouet_id",_html($prod->id));
$xml->demo->addChild("name",_html($prod->name));

foreach($prod->types as $v)
  $xml->demo->addChild("category",ucfirst(_html($v)))->addAttribute("type",_html($v));

$s = new BM_Query();
$s->AddField("prodotherparty.party_compo");
$s->AddField("prodotherparty.party_place");
$s->AddField("prodotherparty.party_year");
$s->AddTable("prodotherparty");
$s->attach(array("prodotherparty"=>"party"),array("parties as party"=>"id"));
$s->AddWhere(sprintf_esc("prod=%d",$prod->id));
$rows = $s->perform();
foreach($rows as $row)
{
  $prod->placings[] = new PouetPlacing( array("party"=>$row->party,"compo"=>$row->party_compo,"ranking"=>$row->party_place,"year"=>$row->party_year) );
}

global $COMPOTYPES;
foreach($prod->placings as $p)
{
  $release = $xml->demo->addChild("release");
  $release->addChild("party",_html($p->party->name))->addAttribute("url",_html($p->party->web));
  $release->addChild("date",$p->year);
  $release->addChild("rank",$p->ranking);
  $release->addChild("compo",$COMPOTYPES[$p->compo]);
}
/*
<release><party url="http://www.ambience.nl">Ambience</party><date>2000-03-15</date><rank>9</rank><compo>pc demo</compo></release>
*/

$xml->demo->addChild("releaseDate",substr($prod->releaseDate,0,7));

if (count($prod->groups))
{
  $xml->demo->addChild("authors");
  foreach($prod->groups as $v)
    $xml->demo->authors->addChild("group",_html($v->name))->addAttribute("pouet_id",$v->id);
}

$xml->demo->addChild("support");
$xml->demo->support->addChild("configuration");
foreach($prod->platforms as $v)
  $xml->demo->support->configuration->addChild("platform",_html($v["name"]))->addAttribute("type",_html($v["slug"]));

$xml->demo->addChild("download");
$xml->demo->download->addChild("url",_html($prod->download))->addAttribute("type","download");

$downloads = SQLLib::SelectRows(sprintf_esc("select * from downloadlinks where prod = %d",$prod->id));
foreach($downloads as $v)
  $xml->demo->download->addChild("url",_html($v->link))->addAttribute("type",$v->type);


$shot = find_screenshot($prod->id);
if ($shot)
{
  $xml->demo->addChild("screenshot");
  $xml->demo->screenshot->addChild("url",_html(POUET_CONTENT_URL.$shot));
}

$dom = dom_import_simplexml($xml)->ownerDocument;
$dom->formatOutput = true;
echo $dom->saveXML();

?>
