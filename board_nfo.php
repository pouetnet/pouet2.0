<?
require_once("bootstrap.inc.php");

class PouetBoxBoardNfo extends PouetBox {
  function PouetBoxBoardNfo() {
    parent::__construct();
    $this->uniqueID = "pouetbox_boardnfo";
    $this->title = "board nfo";
  }

  function LoadFromDB()
  {
///    $this->nfo = SQLLib::SelectRow( sprintf_esc("select * from othernfos where id = %d", $_GET["which"] ) );

    $s = new BM_Query();
    $s->AddField("othernfos.added");
    $s->AddField("othernfos_bbs.name");
    $s->AddTable("othernfos");
    $s->SetLimit(1);
    $s->attach(array("othernfos"=>"adder"),array("users as user"=>"id"));
    $s->attach(array("othernfos"=>"refID"),array("bbses as bbs"=>"id"));
    $s->AddWhere(sprintf_esc("othernfos.id=%d",$_GET["which"]));
    $s->GetQuery();
    list($this->nfo) = $s->perform();
  }
  function RenderHeader()
  {
    echo "\n\n";
    echo "<div class='pouettbl asciiviewer' id='".$this->uniqueID."'>\n";
    echo " <h2><big>"._html($this->nfo->name)."</big>";
    echo "</h2>\n";
  }
  function RenderBody()
  {
    $title = "nfo added by "._html($this->nfo->user->nickname)." on "._html($this->nfo->added);
    echo "<div class='content' title='".$title."'>\n";
    if ($_GET["font"]=="none")
    {
      echo "<pre>";
      $text = file_get_contents( get_local_bbsnfo_path( $_GET["which"] ) );
      $enc = mb_detect_encoding( $text, "iso-8859-1,utf-8" );
      $utf8 = mb_convert_encoding( $text, "utf-8", $enc );
      echo _html( $utf8 );
      echo "</pre>";
    }
    else
      printf("<img src='img_ascii.php?boardnfo=%d&amp;font=%d' alt='nfo'/>\n",$_GET["which"],$_GET["font"]);
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
      $a[] = sprintf("<a href='board_nfo.php?which=%d&amp;font=%s'>%s</a>\n",$_GET["which"],$k,$v);
    echo "[ ".implode(" | \n",$a)." ]";
    echo "  </div>";
    echo "  <div class='foot'>";
    if ($currentUser && $currentUser->IsGloperator())
    {
      //printf("[ <a class='adminlink' href='admin_prod_edit.php?which=%d#files'>update nfo</a> ]\n",$_GET["which"]);
      printf("[ <a class='adminlink' href='%s'>download nfo</a> ]\n",get_local_bbsnfo_path( $_GET["which"] ));
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
