<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/pouet-asciiviewer.php");

class PouetBoxPartyResults extends PouetBoxASCIIViewer
{
  public $party;
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_partyresults";
  }

  function LoadFromDB()
  {
    parent::LoadFromDB();
    
    if (!@$_GET["when"])
    {
      return;
    }
    $this->party = PouetParty::spawn( $_GET["which"] );
    if (!$this->party) 
    {
      return;
    }
    $this->title = $this->party->name." ".(int)$_GET["when"]." results";
  }
  function RenderHeader()
  {
    parent::RenderHeader();
    echo " <h2><big>".$this->party->PrintLinked( $_GET["when"] )." results"."</big></h2>\n";
  }
  function RenderBody()
  {
    $this->asciiFilename = get_local_partyresult_path( $_GET["which"], $_GET["when"] );
    
    parent::RenderBody();
  }
  function RenderFooter()
  {
    parent::RenderFooter();

    global $currentUser;
    echo "  <div class='foot'>";
    if ($currentUser && $currentUser->IsGloperator())
    {
      printf("[ <a class='adminlink' href='admin_party_edition_edit.php?which=%d&amp;when=%d'>update res</a> ]\n",$_GET["which"], $_GET["when"]);
      printf("[ <a class='adminlink' href='%s'>download res</a> ]\n",get_partyresult_url( $_GET["which"], $_GET["when"] ) );
    }
    printf("[ <a href='party.php?which=%d&amp;when=%d'>back to the party</a> ]\n",$_GET["which"],$_GET["when"]);
    echo "  </div>";
    echo "</div>";
  }
};

$box = new PouetBoxPartyResults();
$box->Load();

$TITLE = $box->title;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if ($box->party)
{
  $box->Render();
}

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
