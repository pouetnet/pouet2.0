<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/pouet-asciiviewer.php");

class PouetBoxBoardNfo extends PouetBoxASCIIViewer
{
  public $nfo;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_boardnfo";
    $this->title = "board nfo";
  }

  function LoadFromDB()
  {
    parent::LoadFromDB();
    
    $s = new BM_Query();
    $s->AddField("othernfos.added");
    $s->AddField("othernfos_board.name");
    $s->AddTable("othernfos");
    $s->SetLimit(1);
    $s->attach(array("othernfos"=>"adder"),array("users as user"=>"id"));
    $s->attach(array("othernfos"=>"refID"),array("boards as board"=>"id"));
    $s->AddWhere(sprintf_esc("othernfos.id=%d",$_GET["which"]));
    $s->GetQuery();
    list($this->nfo) = $s->perform();
    
    $this->preferredEncoding = $this->nfo ? @$this->nfo->encoding : null;
  }
  function RenderHeader()
  {
    parent::RenderHeader();
    
    echo " <h2><big>"._html($this->nfo->name)."</big>";
    echo "</h2>\n";
  }
  function RenderBody()
  {
    $this->asciiFilename = get_local_boardnfo_path( $_GET["which"] );;
    $this->bodyTitle = "nfo added by "._html($this->nfo->user->nickname)." on "._html($this->nfo->added);
    
    parent::RenderBody();
  }
  function RenderFooter()
  {
    parent::RenderFooter();
    
    global $currentUser;

    echo "  <div class='foot'>";
    if ($currentUser && $currentUser->IsGloperator())
    {
      //printf("[ <a class='adminlink' href='admin_prod_edit.php?which=%d#files'>update nfo</a> ]\n",$_GET["which"]);
      printf("[ <a class='adminlink' href='%s'>download nfo</a> ]\n",get_boardnfo_url( $_GET["which"] ));
    }
    printf("[ <a href='board.php?which=%d'>back to the board</a> ]\n",$_GET["which"]);
    echo "  </div>";
    echo "</div>";
  }
};

$box = new PouetBoxBoardNfo();
$box->Load();

if ($box->nfo)
  $TITLE = $box->nfo->name;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
