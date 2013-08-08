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
    $a["zxdemo"] = $data["zxdemoID"];
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
    $this->fields["zxdemoID"]["value"] = $group->zxdemo;
  }
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "groups.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditGroup( $_GET["which"] );
$form->Add( "group", $box );

if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit a group: ".$box->group->name;

require_once("include_pouet/header.php");
require_once("include_pouet/menu.inc.php");

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

require_once("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
