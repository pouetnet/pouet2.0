<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-group-submit.php");
require_once("include_pouet/pouet-box-editbase.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("groups.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxAdminEditGroup extends PouetBoxSubmitGroup
{
  function __construct( $id )
  {
    parent::__construct();

    $this->id = (int)$id;

    $this->group = PouetGroup::Spawn( $this->id );

    $this->title = "edit this group: "._html($this->group->name);
  }
  use PouetForm;
  function Commit($data)
  {
    global $groupID;

    $a = array();
    $a["name"] = trim($data["name"]);
    $a["acronym"] = $data["acronym"];
    $a["web"] = $data["website"];
    $a["csdb"] = $data["csdbID"];
//    $a["zxdemo"] = $data["zxdemoID"];
    $a["demozoo"] = $data["demozooID"];
    SQLLib::UpdateRow("groups",$a,"id=".$this->id);

    gloperator_log( "group", $this->id, "group_edit" );

    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();

    $group = $this->group;

    $this->fields["name"]["value"] = $group->name;
    $this->fields["acronym"]["value"] = $group->acronym;
    $this->fields["website"]["value"] = $group->web;
    $this->fields["csdbID"]["value"] = $group->csdb;
    //$this->fields["zxdemoID"]["value"] = $group->zxdemo;
    $this->fields["demozooID"]["value"] = $group->demozoo;
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditGroupAffil extends PouetBoxEditConnectionsBase
{
  public static $slug = "BoardAffil";
  function __construct( $group )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_groupeditaffil";
    $this->group = $group;
    $this->id = $group->id;
    $this->title = "board affiliations links";

    $this->headers = array("board","type");

    $row = SQLLib::selectRow("DESC affiliatedboards type");
    $this->types = enum2array($row->Type);
    
    $s = new BM_Query();
    $s->AddField("affiliatedboards.id");
    $s->AddField("affiliatedboards.type");
    $s->AddTable("affiliatedboards");
    $s->attach(array("affiliatedboards"=>"board"),array("boards as board"=>"id"));
    $s->AddField("affiliatedboards_board.id as affiliatedboards_board_id");
    $s->AddWhere(sprintf_esc("`group`=%d",$this->group->id));
    $this->data = $s->perform();
  }
  use PouetForm;
  function Commit($data)
  {
    if ($data["delBoardAffil"])
    {
      SQLLib::Query("delete from affiliatedboards where id=".(int)$data["delBoardAffil"]);
      gloperator_log( "group", (int)$this->group->id, "group_affil_del" );
      return array();
    }

    $a = array();
    $a["board"] = $data["board"];
    $a["type"] = $data["type"];
    if ($data["editBoardAffilID"])
    {
      SQLLib::UpdateRow("affiliatedboards",$a,"id=".(int)$data["editBoardAffilID"]);
      $a["id"] = $data["editBoardAffilID"];
      gloperator_log( "group", (int)$this->group->id, "group_affil_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["group"] = $this->group->id;
      $a["id"] = SQLLib::InsertRow("affiliatedboards",$a);
      gloperator_log( "group", (int)$this->group->id, "group_affil_add", array("id"=>$a["id"]) );
    }
    if ($data["partial"])
    {
      $o = toObject($a);
      $o->board = PouetBoard::Spawn($a["board"]);
      $this->RenderNormalRow($o);
      $this->RenderNormalRowEnd($o);
      exit();
    }
    return array();
  }
  function RenderEditRow($row)
  {
    echo "    <td><input name='board' value='"._html($row->board->id)."'/></td>\n";
//    echo "    <td><input name='type' value='"._html($row->type)."'/></td>\n";
    echo "    <td><select name='type'>\n";
    foreach($this->types as $v)
      printf("<option%s>%s</option>",$row->type==$v?" selected='selected'":"",_html($v));
    echo "</select></td>\n";
  }
  function RenderNormalRow($v)
  {
    echo "    <td>"._html($v->board->name)."</td>\n";
    echo "    <td>"._html($v->type)."</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script language="JavaScript" type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_groupeditaffil"), "groupBoardAffil", {
    onRowLoad: function(tr){
      new Autocompleter(tr.down("[name='board']"), {"dataUrl":"./ajax_boards.php"});
    }
  } );
});
//-->
</script>
<?
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminDeleteGroup extends PouetBox
{
  function __construct( $group )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_groupdelete";

    $this->classes[] = "errorbox";

    $this->group = $group;

    global $verificationStrings;
    $this->checkString = $verificationStrings[ array_rand($verificationStrings) ];

    $this->title = "delete this group: ".$group->RenderLong();
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
    SQLLib::Query(sprintf_esc("UPDATE prods SET group1=NULL WHERE group1=%d",$this->group->id));
    SQLLib::Query(sprintf_esc("UPDATE prods SET group2=NULL WHERE group2=%d",$this->group->id));
    SQLLib::Query(sprintf_esc("UPDATE prods SET group3=NULL WHERE group3=%d",$this->group->id));
    SQLLib::Query(sprintf_esc("DELETE FROM groupsaka WHERE group1=%d OR group2=%d",$this->group->id,$this->group->id));
    SQLLib::Query(sprintf_esc("DELETE FROM affiliatedboards WHERE `group`=%d",$this->group->id));
    SQLLib::Query(sprintf_esc("DELETE FROM list_items WHERE itemid=%d AND type='group'",$this->group->id));
    SQLLib::Query(sprintf_esc("DELETE FROM groups WHERE id=%d",$this->group->id));
    
    gloperator_log( "group", (int)$this->group->id, "group_delete", get_object_vars($this->group) );

    return array();
  }
  function RenderBody()
  {
    echo "<div class='content'/>";
    echo "  <p>To make sure you want to delete <b>this</b> group, type \"".$this->checkString."\" here:</p>";
    echo "  <input name='checkOrig' type='hidden' value='"._html($this->checkString)."'/>";
    echo "  <input id='check' name='check' autocomplete='no'/>";
    echo "</div>";
    echo "<div class='foot'/>";
    echo "  <input type='submit' value='Submit' />";
    echo "</div>";
    ?>
<script type="text/javascript">
document.observe("dom:loaded",function(){
  $("pouetbox_groupdelete").up("form").observe("submit",function(e){
    if ($F("check") != "<?=_js($this->checkString)?>")
    {
      alert("Enter the verification string!");
      e.stop();
      return;
    }
    if (!confirm("ARE YOU REALLY SURE YOU WANT TO DELETE \"<?=_js($this->group->name)?>\"?!"))
      e.stop();
  });
});
</script>
    <?
  }
}

$boxen = array(
  "PouetBoxAdminEditGroupAffil",
);
if($_GET["partial"] && $currentUser && $currentUser->CanEditItems())
{
  // ajax responses
  $group = new stdClass();
  $group->id = $_GET["which"];
  foreach($boxen as $class)
  {
    if ($_GET["edit" . $class::$slug])
    {
      $box = new $class( $group );
      $box->RenderEditRow( $box->GetRow( $_GET["edit" . $class::$slug] ) );
      $box->RenderEditRowEnd( $box->GetRow( $_GET["edit" . $class::$slug] ) );
    }
    if ($_GET["new" . $class::$slug])
    {
      $box = new $class( $group );
      $box->RenderEditRow( new stdClass() );
      $box->RenderEditRowEnd( new stdClass() );
    }
  }
  exit();
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "groups.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditGroup( $_GET["which"] );
$form->Add( "group", $box );

$form->Add( "groupaffil", new PouetBoxAdminEditGroupAffil( $box->group ) );

$form->Add( "groupdelete", new PouetBoxAdminDeleteGroup( $box->group ) );

if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit a group: ".$box->group->name;

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
