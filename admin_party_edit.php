<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");
include_once("include_pouet/box-party-submit.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("party.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxAdminEditParty extends PouetBoxSubmitParty 
{
  function PouetBoxAdminEditParty( $id ) 
  {
    parent::__construct();

    $this->id = (int)$id;
    
    $this->party = PouetParty::Spawn( $this->id );

    $this->title = "edit this party: "._html( $this->party->name );
  }
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


$form = new PouetFormProcessor();

$form->SetSuccessURL( "party.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditParty( $_GET["which"] );
$form->Add( "party", $box );

if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit a party: ".$box->party->name;

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (get_login_id())
{
  $form->Display();
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
