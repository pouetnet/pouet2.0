<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");
include_once("include_pouet/box-party-edition-submit.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("party.php?which=".(int)$_GET["which"]."&when=".(int)$_GET["when"]);
  exit();
}

class PouetBoxAdminEditPartyEdition extends PouetBoxSubmitPartyEdition 
{
  function PouetBoxAdminEditPartyEdition( $id, $year ) 
  {
    parent::__construct();
    
    $this->id = $id;
    $this->year = $year;
    
    $this->party = PouetParty::Spawn( $this->id );
    
    $this->title = sprintf("edit this party: %s %04d",_html($this->party->name),$this->year);
  }
  function Commit($data) 
  {
    global $partyID;
    
    $sql = array();
    $sql["download"] = $data["download"];
    $sql["csdb"] = $data["csdbID"];
    $sql["slengpung"] = $data["slengpungID"];
    $sql["zxdemo"] = $data["zxdemoID"];
    $sql["artcity"] = $data["artcity"];
        
    $links = SQLLib::selectRow(sprintf_esc("select * from partylinks where party = %d and year = %d",$this->id,$this->year));
    if ($links)
    {
      SQLLib::UpdateRow("partylinks",$sql,sprintf_esc("party = %d and year = %d",$this->id,$this->year));
    }
    else
    {
      $sql["party"] = $this->id;
      $sql["year"] = $this->year;
      SQLLib::InsertRow("partylinks",$sql);
    }
    
    if (is_uploaded_file($_FILES["results"]["tmp_name"]))
    {
      move_uploaded_file($_FILES["results"]["tmp_name"],get_local_partyresult_path($this->id,$this->year));
    }

    gloperator_log( "party", (int)$this->id, "party_edit_links", array("year"=>$this->year) );

    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();
    
    $this->links = SQLLib::selectRow(sprintf_esc("select * from partylinks where party = %d and year = %d",$this->id,$this->year));
    
    $this->fields["download"]["value"] = $this->links->download;
    $this->fields["csdbID"]["value"] = $this->links->csdb;
    $this->fields["slengpungID"]["value"] = $this->links->slengpung;
    $this->fields["zxdemoID"]["value"] = $this->links->zxdemo;
    $this->fields["artcity"]["value"] = $this->links->artcity;
    
    foreach($_POST as $k=>$v)
      if ($this->fields[$k])
        $this->fields[$k]["value"] = $v;
  }
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "party.php?which=".(int)$_GET["which"]."&amp;when=".(int)$_GET["when"], true );

$box = new PouetBoxAdminEditPartyEdition( $_GET["which"],$_GET["when"] );
$form->Add( "partyyear", $box );

if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = sprintf("edit a party edition: %s %04d",$box->party->name,$box->year);

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