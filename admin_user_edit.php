<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

if ($currentUser && !$currentUser->IsModerator())
{
  redirect("user.php?who=".(int)$_GET["who"]);
  exit();
}

class PouetBoxAdminEditUser extends PouetBox
{
  public $id;
  public $user;
  public $sceneID;
  public $formifier;
  public $fields;
  public $levels;
  function __construct( $id )
  {
    parent::__construct();

    $this->id = (int)$id;

    $this->user = PouetUser::Spawn( $this->id );

    $this->uniqueID = "pouetbox_adminedituser";
    $this->title = "edit this user: <a href='user.php?who=".$this->user->id."'>"._html( $this->user->nickname )."</a>";
    $this->sceneID = $this->user->GetSceneIDData( false );
    $this->formifier = new Formifier();
    $this->fields = array();

    $row = SQLLib::selectRow("DESC users level");
    $this->levels = enum2array($row->Type);

  }
  use PouetForm;
  function Commit($data)
  {
    global $currentUser;
    $a = array();
    if ($currentUser->IsAdministrator() && array_search($data["level"],$this->levels)!==false)
      $a["level"] = $data["level"];
    $a["permissionSubmitItems"] = (int)($data["permissionSubmitItems"] == "on");
    $a["permissionOpenBBS"] = (int)($data["permissionOpenBBS"] == "on");
    $a["permissionPostBBS"] = (int)($data["permissionPostBBS"] == "on");
    $a["permissionPostOneliner"] = (int)($data["permissionPostOneliner"] == "on");
    SQLLib::UpdateRow("users",$a,"id=".(int)$this->user->id);
  }
  function LoadFromDB()
  {
    global $currentUser;
    $this->fields = array(
      "lastLogin"=>array(
        "name" => "last logged in",
        "type" => "static",
        "value" => $this->user->lastLogin . " (".dateDiffReadable(time(),$this->user->lastLogin)." ago)",
      ),
      "lastIP"=>array(
        "name" => "last IP",
        "type" => "static",
        "value" => "<a href='admin_user_edit.php?ip=".rawurlencode($this->user->lastip)."'>"._html($this->user->lastip)."</a> ("._html($this->user->lasthost).") [<a href='https://geoiptool.com/en/?IP="._html($this->user->lastip)."'>geoip</a>]",
      ),
      "level"=>array(
        "name" => "level",
        "type" => "select",
        "value" => $this->user->level,
        "fields" => $this->levels,
      ),
      "permissionSubmitItems" => array(
        "name" => "allow user to add items",
        "type" => "checkbox",
        "value" => $this->user->permissionSubmitItems,
      ),
      "permissionOpenBBS" => array(
        "name" => "allow user to open new topics on the BBS",
        "type" => "checkbox",
        "value" => $this->user->permissionOpenBBS,
      ),
      "permissionPostBBS" => array(
        "name" => "allow user to post to the BBS",
        "type" => "checkbox",
        "value" => $this->user->permissionPostBBS,
      ),
      "permissionPostOneliner" => array(
        "name" => "allow user to post in the oneliner",
        "type" => "checkbox",
        "value" => $this->user->permissionPostOneliner,
      ),      
    );
    if ($currentUser && !$currentUser->IsAdministrator())
    {
      $this->fields["level"]["type"] = "static";
    }
  }
  function Render()
  {
    global $currentUser;
    if (!$currentUser)
      return;

    if (!$currentUser->IsModerator())
      return;

    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";

    echo "  <h2>".$this->title."</h2>\n";
    echo "  <div class='content'>\n";
    $this->formifier->RenderForm( $this->fields );
    echo "  </div>\n";

    if ($currentUser->IsAdministrator())
    {
      echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    }
    echo "</div>\n";
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminUserNicks extends PouetBox
{
  public $id;
  public $nicks;
  function __construct( $id )
  {
    parent::__construct();

    $this->id = (int)$id;
    $this->title = "previous nicks";
  }
  use PouetForm;
  function LoadFromDB()
  {
    $this->nicks = SQLLib::SelectRows(sprintf_esc("select * from oldnicks where user = %d",$this->id));
  }
  function RenderBody()
  {
    echo "<ul class='boxlist'>\n";
    foreach($this->nicks as $n) {
      echo "<li>\n";
      echo _html( $n->nick );
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminUserIPs extends PouetBox
{
  public $ip;
  public $users;
  function __construct( $ip )
  {
    parent::__construct();

    $this->ip = $ip;
    $this->title = "users using "._html($ip);
  }
  use PouetForm;
  function LoadFromDB()
  {
    $s = new BM_Query("users");
    $s->AddOrder("users.glops desc");
    $s->AddWhere(sprintf_esc("lastip = '%s'",$this->ip));
    $this->users = $s->perform();
  }
  function RenderBody()
  {
    echo "<ul class='boxlist boxlisttable'>\n";
    foreach($this->users as $p)
	{
      echo "<li>\n";
      echo "<span>\n";
      echo $p->PrintLinkedAvatar()." ";
      echo $p->PrintLinkedName()." ";
      echo "[<a href='admin_user_edit.php?who=".(int)$p->id."'>edit</a>]";
      echo "</span>\n";
      echo "<span>";
      echo $p->glops." glöps";
      echo "</span>\n";
      echo "<span>";
      echo $p->level;
      echo "</span>\n";
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
}

$form = new PouetFormProcessor();

$box = null;
if (is_numeric(@$_GET["who"]))
{
  $form->SetSuccessURL( "user.php?who=".(int)$_GET["who"]."#success", true );

  $box = new PouetBoxAdminEditUser( $_GET["who"] );
  $form->Add( "user", $box );
  $form->Add( "userNicks", new PouetBoxAdminUserNicks( $_GET["who"] ) );
}
else if (@$_GET["ip"])
{
  $form->SetSuccessURL( "user.php?ip=".rawurlencode($_GET["ip"])."#success", true );

  $form->Add( "userIP", new PouetBoxAdminUserIPs( $_GET["ip"] ) );
}

if ($currentUser && $currentUser->IsAdministrator())
  $form->Process();

if ($box && $box->user)
{	
  $TITLE = "edit this user: ".$box->user->nickname;
}
else
{	
  $TITLE = "edit user";
}
	
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
