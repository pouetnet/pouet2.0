<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-group-submit.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("groups.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxAdminEditGroup extends PouetBoxSubmitGroup
{
  function PouetBoxAdminEditGroup( $id )
  {
    parent::__construct();

    $this->id = (int)$id;

    $this->group = PouetGroup::Spawn( $this->id );

    $this->title = "edit this group: "._html($this->group->name);
  }
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

class PouetBoxAdminDeleteGroup extends PouetBox
{
  function PouetBoxAdminDeleteGroup( $group )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_groupdelete";

    $this->classes[] = "errorbox";

    $this->group = $group;

    global $verificationStrings;
    $this->checkString = $verificationStrings[ array_rand($verificationStrings) ];

    $this->title = "delete this group: ".$group->RenderLong();
  }
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
    SQLLib::Query(sprintf_esc("DELETE FROM lists WHERE itemid=%d AND type='group'",$this->group->id));
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


$form = new PouetFormProcessor();

$form->SetSuccessURL( "groups.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditGroup( $_GET["which"] );
$form->Add( "group", $box );

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
