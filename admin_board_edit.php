<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-board-submit.php");
require_once("include_pouet/pouet-box-editbase.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("board.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxAdminEditBoard extends PouetBoxSubmitBoard
{
  public $id;
  public $board;
  function __construct( $id )
  {
    parent::__construct();

    $this->id = (int)$id;

    $this->board = SQLLib::SelectRow(sprintf_esc("SELECT * FROM boards WHERE id = %d", $this->id ) );

    $this->title = "edit this board: <a href='boards.php?which=".$this->id."'>"._html( $this->board->name )."</a>";
  }
  use PouetForm;
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

    SQLLib::Query(sprintf_esc("delete from boards_platforms where board = %d",(int)$this->id));
    if (@$data["platform"])
    {
      $data["platform"] = array_unique($data["platform"]);
      foreach($data["platform"] as $v)
      {
        $a = array();
        $a["board"] = (int)$this->id;
        $a["platform"] = $v;
        SQLLib::InsertRow("boards_platforms",$a);
      }
    }

    gloperator_log( "board", $this->id, "board_edit" );

    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();

    if ($this->board)
    {
      $this->fields["name"]["value"] = $this->board->name;
      $this->fields["sysop"]["value"] = $this->board->sysop;
      $this->fields["phonenumber"]["value"] = $this->board->phonenumber;
      $this->fields["telnetip"]["value"] = $this->board->telnetip;
      $this->fields["started"]["value"] = $this->board->started;
      $this->fields["closed"]["value"] = $this->board->closed;
    }

    $platforms = SQLLib::SelectRows(sprintf_esc("select * from boards_platforms where board = %d",$this->board->id));
    foreach($platforms as $v)
      $this->fields["platform"]["value"][] = $v->platform;
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditBoardAffil extends PouetBoxEditConnectionsBase
{
  public $id;
  public $board;
  public $headers;
  public $types;
  public static $slug = "BoardAffil";
  function __construct( $board )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_boardeditaffil";
    $this->board = $board;
    $this->id = $board->id;
    $this->title = "board affiliations links";

    $this->headers = array("board","type");

    $row = SQLLib::selectRow("DESC affiliatedboards type");
    $this->types = enum2array($row->Type);
    
    $s = new BM_Query();
    $s->AddField("affiliatedboards.id");
    $s->AddField("affiliatedboards.type");
    $s->AddTable("affiliatedboards");
    $s->attach(array("affiliatedboards"=>"group"),array("groups as group"=>"id"));
    $s->AddField("affiliatedboards_group.id as affiliatedboards_group_id");
    $s->AddWhere(sprintf_esc("`board`=%d",$this->board->id));
    $this->data = $s->perform();
  }
  use PouetForm;
  function Commit($data)
  {
    if (@$data["delBoardAffil"])
    {
      SQLLib::Query("delete from affiliatedboards where id=".(int)$data["delBoardAffil"]);
      gloperator_log( "board", (int)$this->board->id, "board_affil_del" );
      return array();
    }

    $a = array();
    $a["group"] = $data["group"];
    $a["type"] = $data["type"];
    if (@$data["editBoardAffilID"])
    {
      SQLLib::UpdateRow("affiliatedboards",$a,"id=".(int)$data["editBoardAffilID"]);
      $a["id"] = $data["editBoardAffilID"];
      gloperator_log( "board", (int)$this->board->id, "board_affil_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["board"] = $this->board->id;
      $a["id"] = SQLLib::InsertRow("affiliatedboards",$a);
      gloperator_log( "board", (int)$this->board->id, "board_affil_add", array("id"=>$a["id"]) );
    }
    if (@$data["partial"])
    {
      $o = toObject($a);
      $o->group = PouetGroup::Spawn($a["group"]);
      $this->RenderNormalRow($o);
      $this->RenderNormalRowEnd($o);
      exit();
    }
    return array();
  }
  function RenderEditRow($row = null)
  {
    echo "    <td><input name='group' value='"._html($row ? $row->group->id : "")."'/></td>\n";
    echo "    <td><select name='type'>\n";
    foreach($this->types as $v)
      printf("<option%s>%s</option>",($row&&$row->type==$v)?" selected='selected'":"",_html($v));
    echo "</select></td>\n";
  }
  function RenderNormalRow($v)
  {
    echo "    <td>".$v->group->RenderLong()."</td>\n";
    echo "    <td>"._html($v->type)."</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_boardeditaffil"), "groupBoardAffil", {
    onRowLoad: function(tr){
      new Autocompleter(tr.down("[name='group']"), {"dataUrl":"./ajax_groups.php","processRow": function(item) {
        return item.name.escapeHTML() + (item.disambiguation ? " <span class='group-disambig'>" + item.disambiguation.escapeHTML() + "</span>" : "");
      }});
    }
  } );
});
//-->
</script>
<?php
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminDeleteBoard extends PouetBox
{
  public $board;
  public $checkString;
  function __construct( $board )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_boarddelete";

    $this->classes[] = "errorbox";

    $this->board = $board;

    global $verificationStrings;
    $this->checkString = $verificationStrings[ array_rand($verificationStrings) ];

    $this->title = "delete this board: "._html($board->name);
  }
  use PouetForm;
  function Validate($data)
  {
    if ($data["check"] != $data["checkOrig"])
      return array("wrong verification string !");
    return array();
  }
  function Commit($data)
  {
    SQLLib::Query(sprintf_esc("DELETE FROM othernfos WHERE refid=%d AND type='bbs'",$this->board->id)); // TODO: cleanup files
    SQLLib::Query(sprintf_esc("DELETE FROM affiliatedboards WHERE board=%d",$this->board->id));
    SQLLib::Query(sprintf_esc("DELETE FROM boards_platforms WHERE board=%d",$this->board->id));
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
<script>
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
    <?php
  }
}

///////////////////////////////////////////////////////////////////////////////

$boxen = array(
  "PouetBoxAdminEditBoardAffil",
);
if(@$_GET["partial"] && $currentUser && $currentUser->CanEditItems())
{
  // ajax responses
  $group = new stdClass();
  $group->id = $_GET["which"];
  foreach($boxen as $class)
  {
    $box = new $class( $group );
    $box->RenderPartialResponse();
  }
  exit();
}


$form = new PouetFormProcessor();

$form->SetSuccessURL( "boards.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditBoard( $_GET["which"] );
$form->Add( "board", $box );
$form->Add( "boardaffil", new PouetBoxAdminEditBoardAffil( $box->board ) );
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
