<?
require_once("../bootstrap.inc.php");

header("Content-type: application/xml; charset=utf-8");
//header("Content-type: text/plain; charset=utf-8");

$xml = new SimpleXMLElement("<"."?xml version='1.0' encoding='UTF-8'?"."><xnfo/>");

$prod = PouetProd::Spawn( $_GET["which"] );

$a = array(&$prod);
PouetCollectPlatforms( $a );
    
if (!$prod)
  die($xml->AsXML());

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
$s->AddField("prodotherparty.partycompo");
$s->AddField("prodotherparty.party_place");
$s->AddField("prodotherparty.party_year");
$s->AddTable("prodotherparty");
$s->attach(array("prodotherparty"=>"party"),array("parties as party"=>"id"));
$s->AddWhere(sprintf_esc("prod=%d",$prod->id));
$rows = $s->perform();
foreach($rows as $row)
{
  $prod->placings[] = new PouetPlacing( array("party"=>$row->party,"compo"=>$row->partycompo,"ranking"=>$row->party_place,"year"=>$row->party_year) );
}

foreach($prod->placings as $p)
{
  $release = $xml->demo->addChild("release");
  $release->addChild("party",$p->party->name)->addAttribute("url",$p->party->web);
  $release->addChild("date",$p->year);
  $release->addChild("rank",$p->ranking);
  $release->addChild("compo",$p->compo);
}
/*
<release><party url="http://www.ambience.nl">Ambience</party><date>2000-03-15</date><rank>9</rank><compo>pc demo</compo></release>
*/

$xml->demo->addChild("releaseDate",substr($prod->date,0,10));

if (count($prod->groups))
{
  $xml->demo->addChild("authors");
  foreach($prod->groups as $v)
    $xml->demo->authors->addChild("groups",_html($v->name))->addAttribute("pouet_id",$v->id);
}

$xml->demo->addChild("support");
$xml->demo->support->addChild("configuration");
foreach($prod->platforms as $v)
  $xml->demo->support->configuration->addChild("platform",$PLATFORMS[$v]["name"])->addAttribute("type",$PLATFORMS[$v]["slug"]);

$xml->demo->addChild("download");
$xml->demo->download->addChild("url",$prod->download)->addAttribute("type","download");

$downloads = SQLLib::SelectRows(sprintf_esc("select * from downloadlinks where prod = %d",$prod->id));
foreach($downloads as $v)
  $xml->demo->download->addChild("url",$v->link)->addAttribute("type",$v->type);


$shot = find_screenshot($prod->id);
if ($shot)
{
  $xml->demo->addChild("screenshot");
  $xml->demo->screenshot->addChild("url",POUET_ROOT_URL.$shot);
}

$dom = dom_import_simplexml($xml)->ownerDocument;
$dom->formatOutput = true;
echo $dom->saveXML();

?>
