<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");
include_once("include_pouet/box-group-submit.php");

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

$box = new PouetBoxAdminEditGroup( $_GET["which"] );

$TITLE = "edit a group: "._html($box->group->name);

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (get_login_id())
{
  $showBox = true;
  $errors = array();
  if ($_POST)
  {
    $errors = $box->ParsePostMessage( $_POST );
    if (count($errors))
    {
      $msg = new PouetBoxModalMessage( true );
      $msg->classes[] = "errorbox";
      $msg->title = "An error has occured:";
      $msg->message = "<ul><li>".implode("</li><li>",$errors)."</li></ul>";
      $msg->Render();
    }
    else
    {
      $msg = new PouetBoxModalMessage( true );
      $msg->classes[] = "successbox";
      $msg->title = "Success!";
      $msg->message = "<a href='groups.php?which=".(int)$_GET["which"]."'>see what you've done</a>";
      $msg->Render();
      $showBox = false;
    }
  }

  $box->Load();
  if ($showBox)
  {
    printf("<form action='%s' method='post' enctype='multipart/form-data'>\n",_html(selfPath()));
    $box->Render();
    printf("</form>");
  }

}
else
{
  include_once("include_pouet/box-login.php");
  $box = new PouetBoxLogin();
  $box->Render();
}

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");

?>