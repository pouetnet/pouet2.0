<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-party-submit.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("party.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxAdminEditParty extends PouetBoxSubmitParty
{
  public $id;
  public $party;
  function __construct( $id )
  {
    parent::__construct();

    $this->id = (int)$id;

    $this->party = PouetParty::Spawn( $this->id );

    $this->title = "edit this party: ". $this->party->PrintLinked();
  }
  use PouetForm;
  function Commit($data)
  {
    global $partyID;

    $a = array();
    $a["name"] = trim($data["name"]);
    $a["web"] = $data["website"];
    SQLLib::UpdateRow("parties",$a,"id=".$this->id);

    gloperator_log( "party", $this->id, "party_edit" );

    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();

    $this->fields["name"]["value"] = $this->party->name;
    $this->fields["website"]["value"] = $this->party->web;
  }
}

class PouetBoxAdminDeleteParty extends PouetBox
{
  public $party;
  public $checkString;
  function __construct( $party )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_partydelete";

    $this->classes[] = "errorbox";

    $this->party = $party;

    global $verificationStrings;
    $this->checkString = $verificationStrings[ array_rand($verificationStrings) ];

    $this->title = "delete this party: ".$party->PrintLinked();
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
    $this->party->Delete();
    
    gloperator_log( "party", (int)$this->party->id, "party_delete", get_object_vars($this->party) );

    return array();
  }
  function RenderBody()
  {
    echo "<div class='content'/>";
    echo "  <p>To make sure you want to delete <b>this</b> party, type \"".$this->checkString."\" here:</p>";
    echo "  <input name='checkOrig' type='hidden' value='"._html($this->checkString)."'/>";
    echo "  <input id='check' name='check' autocomplete='no'/>";
    echo "</div>";
    echo "<div class='foot'/>";
    echo "  <input type='submit' value='Submit' />";
    echo "</div>";
    ?>
<script>
document.observe("dom:loaded",function(){
  $("pouetbox_partydelete").up("form").observe("submit",function(e){
    if ($F("check") != "<?=_js($this->checkString)?>")
    {
      alert("Enter the verification string!");
      e.stop();
      return;
    }
    if (!confirm("ARE YOU REALLY SURE YOU WANT TO DELETE \"<?=_js($this->party->name)?>\"?!"))
      e.stop();
  });
});
</script>
    <?php
  }
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "party.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditParty( $_GET["which"] );
$form->Add( "party", $box );

$form->Add( "partydelete", new PouetBoxAdminDeleteParty( $box->party ) );

if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit a party: ".$box->party->name;

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
