<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-bbs-post.php");

class PouetBoxMirrors extends PouetBox
{
  public $prod;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_mirrors";
  }

  function LoadFromDB() 
  {
    $this->prod = PouetProd::spawn(@$_GET["which"]);
    if (!$this->prod) return;

    $a = array(&$this->prod);
    PouetCollectPlatforms( $a );

    $this->title = "mirrors :: ".$this->prod->name;
  }

  function RenderContent()
  {
    echo "got a 404 or the server is still having its morning coffee? try one of these mirror lists:";

    $baseName = basename($this->prod->download);
    $somepos = strrpos($baseName, ".");
    if ($somepos === false) 
    { 
      // not found means it is extensionless, cool for amiga stuff
      $extensionless = $baseName;
    } 
    else 
    { 
      //lets strip the extension to help searches for prods using .rar instead of .zip
      $extensionless = substr($baseName, 0, $somepos);
    }

    $extensionless = rawurlencode($extensionless);

    $links = array();

    $links["https://files.scene.org/search/?q=".$extensionless.""] = $this->prod->name." on scene.org"; //(works now! [in theory])
    $links["https://www.google.com/search?q=\"".$extensionless."\""] = $this->prod->name . " on google";
//    $links["http://www.filesearching.com/cgi-bin/s?q=".$extensionless.""] = $this->prod->name . " on filesearching.com";
//    $links["http://www.filemirrors.com/search.src?file=".$extensionless.""] = $this->prod->name . " on filemirrors";
    $links["https://hornet.org/cgi-bin/scene-search.cgi?search=".$extensionless.""] = $this->prod->name . " on the hornet archive";
    $links["https://web.archive.org/web/*/".$this->prod->download] = $this->prod->name." on the wayback machine";
    $links["http://pouet-mirror.sesse.net/*/".$this->prod->download] = $this->prod->name." on Sesse's pou\xC3\xABt.net mirror";

    $hasAmiga = false;
    foreach($this->prod->platforms as $v)
    {
      if (stristr($v["name"],"amiga")!==false)
      {
        $hasAmiga = true;
      }
    }

    if ($hasAmiga)
    {
      $links["http://aminet.net/search.php?query=".$extensionless.""] = $this->prod->name . " on aminet (new)";
      //$links["http://uk.aminet.net/aminetbin/find?".$extensionless.""] = $this->prod->name . " on aminet (uk)";
      //$links["http://de.aminet.net/aminetbin/find?".$extensionless.""] = $this->prod->name . " on aminet (de)";
      //$links["http://no.aminet.net/aminetbin/find?".$extensionless.""] = $this->prod->name . " on aminet (no)";
      $links["http://amigascne.org/cgi-bin/search.cgi?searchstr=".$extensionless.""] = $this->prod->name . " on amigascne.org";
    }
    if (array_search("cracktro",$this->prod->types)!==false)
    {
      $links["https://defacto2.net/search/result?search=files&query=".$extensionless.""] = $this->prod->name . " on defacto2";
    }
    echo "<ul>\n";
    foreach($links as $url=>$desc)
    {
      printf("<li><a href='%s'>%s</a></li>",_html($url),_html($desc));
    }
    echo "</ul>\n";
  }
  function RenderFooter()
  {
    echo "  <div class='foot'><a href='prod.php?which=".$this->prod->id."'>back to "._html($this->prod->name)."</a></div>\n";
    echo "</div>\n";
  }

};

$p = new PouetBoxMirrors();
$p->Load();

$TITLE = $p->title;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
if ($p->prod)
  echo $p->Render();
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
