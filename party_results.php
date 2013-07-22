<?
include_once("bootstrap.inc.php");

class PouetBoxPartyResults extends PouetBox {
  function PouetBoxPartyResults() {
    parent::__construct();
    $this->uniqueID = "pouetbox_partyresults";
  }
  
  function LoadFromDB()
  {
    $this->party = PouetParty::spawn( $_GET["which"] );
    $this->title = $this->party->name." ".(int)$_GET["when"]." results";
  }
  function RenderHeader()
  {
    echo "\n\n";
    echo "<div class='pouettbl asciiviewer' id='".$this->uniqueID."'>\n";
    echo " <h2><big>"._html($this->title)."</big></h2>\n";
  }
  function RenderContent() 
  {
    if ($_GET["font"]=="none")
    {
      echo "<pre>";
      echo _html( file_get_contents( get_local_partyresult_path( $_GET["which"], $_GET["when"] ) ) );
      echo "</pre>";
    }
    else
      printf("<img src='img_ascii.php?results=%d&amp;year=%d&amp;font=%d' alt='nfo'/>\n",$_GET["which"],$_GET["when"],$_GET["font"]);
  }
  function RenderFooter()
  {
    global $currentUser;
    
    echo "  <div class='content' id='fontlist'>";
    $fonts = array(
      "none" => "html",
      "1" => "dos 80*25",
      "2" => "dos 80*50",
      "3" => "rez's ascii",
      "4" => "amiga medres",
      "5" => "amiga hires",
    );
    foreach($fonts as $k=>$v)
      $a[] = sprintf("<a href='party_results.php?which=%d&amp;when=%d&amp;font=%s'>%s</a>\n",$_GET["which"],$_GET["when"],$k,$v);
    echo "[ ".implode(" | \n",$a)." ]";
    echo "  </div>";
    echo "  <div class='foot'>";
    if ($currentUser && $currentUser->IsGloperator())
    {
      printf("[ <a class='adminlink' href='admin_party_edition_edit.php?which=%d&amp;when=%d'>update res</a> ]\n",$_GET["which"], $_GET["when"]);
      printf("[ <a class='adminlink' href='%s'>download res</a> ]\n",get_local_partyresult_path( $_GET["which"], $_GET["when"] ) );
    }
    printf("[ <a href='party.php?which=%d&amp;when=%d'>back to the party</a> ]\n",$_GET["which"],$_GET["when"]);
    echo "  </div>";
    echo "</div>";
  }
};

$box = new PouetBoxPartyResults();
$box->Load();

$TITLE = $box->title;

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box->Render();

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");

?>