<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

if ($currentUser && !$currentUser->CanSubmitItems())
{
  redirect("index.php");
  exit();
}

class PouetBoxSubmitList extends PouetBox
{
  public $formifier;
  public $fields;

  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_submitlist";
    $this->title = "create a new list!";
    $this->formifier = new Formifier();
    $this->fields = array(
      "name"=>array(
        "name" => "list name",
        "info" => " ",
        "required" => true,
      ),
      "desc"=>array(
        "name" => "short description",
        "type" => "textarea",
        "info" => "be concise."
      ),
    );
  }
  use PouetForm;
  function Validate( $data )
  {
    global $partyID,$currentUser;

    if (!$currentUser)
      return array("you have to be logged in !");

    if (!$currentUser->CanSubmitItems())
      return array("not allowed lol !");

    if (!trim($data["name"]))
    {
      return array("oh come on you're more creative than that !");
    }
    return array();
  }
  function Commit( $data )
  {
    $a = array();
    $a["name"] = trim($data["name"]);
    $a["desc"] = $data["desc"];
    $a["owner"] = get_login_id();
    $a["addedUser"] = get_login_id();
    $a["addedDate"] = date("Y-m-d H:i:s");
    $this->listID = SQLLib::InsertRow("lists",$a);

    @unlink("cache/pouetbox_latestlists.cache");
    
    return array();
  }
  function GetInsertionID()
  {
    return $this->listID;
  }

  function LoadFromDB()
  {
  }

  function Render()
  {
    global $partyID,$currentUser;

    if (!$currentUser)
      return;

    if (!$currentUser->CanSubmitItems())
      return;

    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";

    echo "  <h2>".$this->title."</h2>\n";
    echo "  <div class='content'>\n";
    $this->formifier->RenderForm( $this->fields );
    echo "  </div>\n";

    echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};

$TITLE = "create a new list";

$form = new PouetFormProcessor();

$form->SetSuccessURL( "lists.php?which={%NEWID%}", true );

$form->Add( "list", new PouetBoxSubmitList() );

if ($currentUser && $currentUser->CanSubmitItems())
  $form->Process();

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
