<?php
class PouetRSS
{
  private $xml;
  private $dtd;
  private $dom;
  function __construct( $opt = array() )
  {
    if (!class_exists("SimpleXMLElement"))
    {
      return;
    }
    $this->xml = new SimpleXMLElement("<"."?xml version='1.0' encoding='UTF-8'?"."><rss/>");
    $this->dtd = POUET_ROOT_URL . "faq.php#faq12";
    
    $this->dom = dom_import_simplexml($this->xml);

    $this->dom->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:pouet",$this->dtd);
    $this->dom->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:atom","http://www.w3.org/2005/Atom");
    
    $this->xml->addAttribute("version","2.0");
    $this->xml->addChild("channel");
    $this->xml->channel->addChild("title",@$opt["title"] ?: "pouët.net");
    $this->xml->channel->addChild("link",@$opt["link"] ?: POUET_ROOT_URL);
    $this->xml->channel->addChild("description",@$opt["description"] ?: "your online demoscene resource");

    $link = $this->xml->channel->addChild("atom:link","","http://www.w3.org/2005/Atom");
    $link->addAttribute("href",(@$_SERVER["HTTPS"]=="on"?"https":"http")."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] );
    $link->addAttribute("rel","self");
    $link->addAttribute("type","application/rss+xml");
  }
  function AddItem( $params )
  {
    if (!class_exists("SimpleXMLElement"))
    {
      return;
    }
    if (!@$params["guid"])
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
          $node->addChild($k,_html($v))->addAttribute("isPermaLink",strstr($v,"://")===false?"false":"true");
          break;
        default:
          if (is_array($v))
          {
            foreach($v as $i)
              $node->addChild($k,_html($i),strstr($k,":")!==false ? $this->dtd : null);
          }
          else
          {
            $node->addChild($k,_html($v),strstr($k,":")!==false ? $this->dtd : null);
          }
          break;
      }
    }
  }
  function Render()
  {
    if (!class_exists("SimpleXMLElement"))
    {
      return;
    }
    header("Content-type: application/rss+xml; charset=utf-8");
    //header("Content-type: text/plain; charset=utf-8");

    //echo $this->xml->asXML();
    $dom = dom_import_simplexml($this->xml)->ownerDocument;
    $dom->formatOutput = true;
    echo $dom->saveXML();
  }
};

?>
