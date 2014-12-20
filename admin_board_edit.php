<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-board-submit.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("board.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxAdminEditBoard extends PouetBoxSubmitBoard
{
  function PouetBoxAdminEditBoard( $id )
  {
    parent::__construct();

    $this->id = (int)$id;

    $this->board = SQLLib::SelectRow(sprintf_esc("SELECT * FROM boards WHERE id = %d", $this->id ) );

    $this->title = "edit this board: <a href='boards.php?which=".$this->id."'>"._html( $this->board->name )."</a>";
  }
  function Commit($data)
  {
    global $boardID;

    $a = array();
    $a["name"] = trim($data["name"]);
    $a["sysop"] = trim($data["sysop"]);
    
    if( $data["started_year"] && $data["started_month"] && checkdate( (int)$data["started_month"], 15, (int)$data["started_year"]) )
      $a["started"] = sprintf("%04d-%02d-15",$data["started_year"],$data["started_month"]);
    else if ($data["started_year"])
      $a["started"] = sprintf("%04d-00-15",$data["started_year"]);

    if( $data["closed_year"] && $data["closed_month"] && checkdate( (int)$data["closed_month"], 15, (int)$data["closed_year"]) )
      $a["closed"] = sprintf("%04d-%02d-15",$data["closed_year"],$data["closed_month"]);
    else if ($data["closed_year"])
      $a["closed"] = sprintf("%04d-00-15",$data["closed_year"]);
    
    $a["phonenumber"] = trim($data["phonenumber"]);
    $a["telnetip"] = trim($data["telnetip"]);
    
    SQLLib::UpdateRow("boards",$a,"id=".$this->id);

    $data["platform"] = array_unique($data["platform"]);
    SQLLib::Query(sprintf_esc("delete from boards_platforms where board = %d",(int)$this->id));
    foreach($data["platform"] as $v)
    {
      $a = array();
      $a["board"] = (int)$this->id;
      $a["platform"] = $v;
      SQLLib::InsertRow("boards_platforms",$a);
    }

    gloperator_log( "board", $this->id, "board_edit" );

    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();

    $this->fields["name"]["value"] = $this->board->name;
    $this->fields["sysop"]["value"] = $this->board->sysop;
    $this->fields["phonenumber"]["value"] = $this->board->phonenumber;
    $this->fields["telnetip"]["value"] = $this->board->telnetip;
    $this->fields["started"]["value"] = $this->board->started;
    $this->fields["closed"]["value"] = $this->board->closed;

    $platforms = SQLLib::SelectRows(sprintf_esc("select * from boards_platforms where board = %d",$this->board->id));
    foreach($platforms as $v)
      $this->fields["platform"]["value"][] = $v->platform;
  }
}

class PouetBoxAdminDeleteBoard extends PouetBox
{
  function PouetBoxAdminDeleteBoard( $board )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_boarddelete";

    $this->classes[] = "errorbox";

    $this->board = $board;

    global $verificationStrings;
    $this->checkString = $verificationStrings[ array_rand($verificationStrings) ];

    $this->title = "delete this board: "._html($board->name);
  }
  function Validate($data)
  {
    if ($data["check"] != $data["checkOrig"])
      return array("wrong verification string !");
    return array();
  }
  function Commit($data)
  {
    // TODO
    SQLLib::Query(sprintf_esc("DELETE FROM boards WHERE id=%d",$this->board->id));
    
    gloperator_log( "board", (int)$this->board->id, "board_delete", get_object_vars($this->board) );

    return array();
  }
  function RenderBody()
  {
    echo "<div class='content'/>";
    echo "  <p>To make sure you want to delete <b>this</b> board, type \"".$this->checkString."\" here:</p>";
    echo "  <input name='checkOrig' type='hidden' value='"._html($this->checkString)."'/>";
    echo "  <input id='check' name='check' autocomplete='no'/>";
    echo "</div>";
    echo "<div class='foot'/>";
    echo "  <input type='submit' value='Submit' />";
    echo "</div>";
    ?>
<script type="text/javascript">
document.observe("dom:loaded",function(){
  $("pouetbox_boarddelete").up("form").observe("submit",function(e){
    if ($F("check") != "<?=_js($this->checkString)?>")
    {
      alert("Enter the verification string!");
      e.stop();
      return;
    }
    if (!confirm("ARE YOU REALLY SURE YOU WANT TO DELETE \"<?=_js($this->board->name)?>\"?!"))
      e.stop();
  });
});
</script>
    <?
  }
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "boards.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditBoard( $_GET["which"] );
$form->Add( "board", $box );

$form->Add( "boarddelete", new PouetBoxAdminDeleteBoard( $box->board ) );

if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit a board: ".$box->board->name;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (get_login_id())
{
  $form->Display();
}
else
{
  require_once("include_pouet/box-login.php");
  $box = new PouetBoxLogin();
  $box->Render();
}

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
