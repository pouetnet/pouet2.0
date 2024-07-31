<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/pouet-asciiviewer.php");

class PouetBoxProdNfo extends PouetBoxASCIIViewer
{
  public $prod;
  public $nfo;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodnfo";
    $this->title = "prod nfo";
  }

  function LoadFromDB()
  {
    parent::LoadFromDB();

    $this->prod = PouetProd::spawn( $_GET["which"] );
    if (!$this->prod)
    {
      return;
    }

    $s = new BM_Query();
    $s->AddField("added");
    $s->AddTable("nfos");
    $s->SetLimit(1);
    $s->attach(array("nfos"=>"user"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("prod=%d",$this->prod->id));
    $rows = $s->perform();
    $this->nfo = $rows ? reset($rows) : null;

    $this->preferredEncoding = $this->nfo ? @$this->nfo->encoding : null;
  }
  function RenderHeader()
  {
    parent::RenderHeader();

    echo " <h2><big>".$this->prod->RenderLink()."</big>";
    if ($this->prod->groups)
      echo " by ".$this->prod->RenderGroupsLong();
    echo "</h2>\n";
  }
  function RenderBody()
  {
    $this->asciiFilename = get_local_nfo_path( (int)$_GET["which"] );
    if ($this->nfo)
    {
      $this->bodyTitle = "nfo added by "._html($this->nfo->user->nickname)." on "._html($this->nfo->added);
    }

    parent::RenderBody();
  }
  function RenderFooter()
  {
    parent::RenderFooter();

    global $currentUser;
    echo "  <div class='foot'>";
    if ($currentUser && $currentUser->IsGloperator())
    {
      printf("[ <a class='adminlink' href='admin_prod_edit.php?which=%d#files'>update nfo</a> ]\n",$_GET["which"]);
      printf("[ <a class='adminlink' href='%s'>download nfo</a> ]\n",get_nfo_url( $_GET["which"] ));
    }
    printf("[ <a href='prod.php?which=%d'>back to the prod</a> ]\n",$_GET["which"]);
    echo "  </div>";
    echo "</div>";
  }
};

$box = new PouetBoxProdNfo();
$box->Load();

if ($box->prod)
  $TITLE = $box->prod->name.($box->prod->groups ? " by ".$box->prod->RenderGroupsPlain() : "")." :: nfo";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if ($box->prod)
{
  $box->Render();
}
else
{
  echo "ó, te zengővirágillatfelhőben illatozó, trabantautószárnyascsikó, bénaságtiportlelkű, röppenőszárnyú lovasa!";
}

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
