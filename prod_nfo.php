<?
require_once("bootstrap.inc.php");

class PouetBoxProdNfo extends PouetBox {
  function __construct() {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodnfo";
    $this->title = "prod nfo";
  }

  function LoadFromDB()
  {
    $this->prod = PouetProd::spawn( $_GET["which"] );
    $this->fonts = array(
      "none" => array(
        "name"=>"html",
        "class"=>"",
      ),
      "1" => array(
        "name"=>"dos 80*25",
        "class"=>"dos-80x25",
      ),
      "2" => array(
        "name"=>"dos 80*50",
        "class"=>"dos-80x50",
      ),
      "3" => array(
        "name"=>"rez's ascii",
        "image"=>true,
      ),
      "4" => array(
        "name"=>"amiga medres",
        "class"=>"amiga-medres",
      ),
      "5" => array(
        "name"=>"amiga hires",
        "class"=>"amiga-hires",
      ),
    );

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
    if (!$this->fonts[$_GET["font"]]["image"])
    {
      printf("<pre class='%s'>",_html($this->fonts[$_GET["font"]]["class"]));
      $text = file_get_contents( get_local_nfo_path( $_GET["which"] ) );
      echo _html( process_ascii( $text ) );
      printf("</pre>");
    }
    else
    {
      printf("<img src='img_ascii.php?nfo=%d&amp;font=%d' alt='nfo'/>\n",$_GET["which"],$_GET["font"]);
    }
    echo "</div>\n";
  }
  function RenderFooter()
  {
    global $currentUser;

    echo "  <div class='content' id='fontlist'>";
    foreach($this->fonts as $k=>$v)
    {
      $a[] = sprintf("<a href='prod_nfo.php?which=%d&amp;font=%s'>%s</a>\n",$_GET["which"],$k,$v["name"]);
    }
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
