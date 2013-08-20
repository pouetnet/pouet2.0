<?
require_once("bootstrap.inc.php");

class PouetBoxProdNfo extends PouetBox {
  function PouetBoxProdNfo() {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodnfo";
    $this->title = "prod nfo";
  }

  function LoadFromDB()
  {
    $this->prod = PouetProd::spawn( $_GET["which"] );

    $s = new BM_Query();
    $s->AddField("added");
    $s->AddTable("nfos");
    $s->SetLimit(1);
    $s->attach(array("nfos"=>"user"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("prod=%d",$this->prod->id));
    list($this->nfo) = $s->perform();
  }
  function RenderHeader()
  {
    echo "\n\n";
    echo "<div class='pouettbl asciiviewer' id='".$this->uniqueID."'>\n";
    echo " <h2><big>".$this->prod->RenderLink()."</big>";
    if ($this->prod->groups)
      echo " by ".$this->prod->RenderGroupsLong();
    echo "</h2>\n";
  }
  function RenderBody()
  {
    $title = "nfo added by "._html($this->nfo->user->nickname)." on "._html($this->nfo->added);
    echo "<div class='content' title='".$title."'>\n";
    if ($_GET["font"]=="none")
    {
      echo "<pre>";
      $text = file_get_contents( get_local_nfo_path( $_GET["which"] ) );
      echo _html( process_ascii( $text ) );
      echo "</pre>";
    }
    else
      printf("<img src='img_ascii.php?nfo=%d&amp;font=%d' alt='nfo'/>\n",$_GET["which"],$_GET["font"]);
    echo "</div>\n";
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
      $a[] = sprintf("<a href='prod_nfo.php?which=%d&amp;font=%s'>%s</a>\n",$_GET["which"],$k,$v);
    echo "[ ".implode(" | \n",$a)." ]";
    echo "  </div>";
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

$box->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
