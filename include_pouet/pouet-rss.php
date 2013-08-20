<?
class PouetRSS
{
  private $xml;
  function __construct()
  {
    $this->xml = new SimpleXMLElement("<"."?xml version='1.0' encoding='UTF-8'?"."><rss/>");
    $this->xml->addAttribute("version","2.0");
    $this->xml->addChild("channel");
    $this->xml->channel->addChild("title","pouët.net");
    $this->xml->channel->addChild("link",POUET_ROOT_URL);
    $this->xml->channel->addChild("description","your online demoscene resource");

    $link = $this->xml->channel->addChild("atom:link","","http://www.w3.org/2005/Atom");
    $link->addAttribute("href",($_SERVER["HTTPS"]=="on"?"https":"http")."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] );
    $link->addAttribute("rel","self");
    $link->addAttribute("type","application/rss+xml");
  }
  function AddItem( $params )
  {
    if (!$params["guid"])
      $params["guid"] = $params["link"];
    $node = $this->xml->channel->addChild("item");
    foreach($params as $k=>$v)
    {
      if(!$v) continue;
      switch($k)
      {
        case "enclosure":
          {
            $data = getimagesize(POUET_CONTENT_LOCAL . "/" . $v);
            $enc = $node->addChild("enclosure");
            $enc->addAttribute("url",POUET_CONTENT_URL . $v);
            $enc->addAttribute("length",filesize(POUET_CONTENT_LOCAL . "/" . $v));
            if ($data && $data["mime"])
              $enc->addAttribute("type",$data["mime"]);
          } break;
        case "guid":
          $node->addChild($k,$v)->addAttribute("isPermaLink",strstr($v,"://")===false?"false":"true");
          break;
        default:
          $node->addChild($k,$v);
          break;
      }
    }
  }
  function Render()
  {
    header("Content-type: application/rss+xml; charset=utf-8");
    //header("Content-type: text/plain; charset=utf-8");

    //echo $this->xml->asXML();
    $dom = dom_import_simplexml($this->xml)->ownerDocument;
    $dom->formatOutput = true;
    echo $dom->saveXML();
  }
};

?>
