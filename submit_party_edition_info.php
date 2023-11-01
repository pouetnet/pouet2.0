<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-prod-submit.php");
require_once("include_pouet/box-party-edition-submit.php");

if ($currentUser && !$currentUser->CanSubmitItems())
{
  redirect("party.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxSubmitPartyEditionInfo extends PouetBoxSubmitPartyEdition
{
  public $id;
  public $year;
  public $party;
  public $links;
  public $prods;
  function __construct( $id, $year )
  {
    parent::__construct();

    $this->id = (int)$id;
    $this->year = (int)$year;

    $this->party = PouetParty::Spawn( $this->id );

    $this->prods = SQLLib::selectRow(sprintf_esc("select * from prods where party = %d and party_year = %d limit 1",$this->id,$this->year));

    $this->title = sprintf("submit links for this party: %s %04d",_html($this->party->name),$this->year);
  }
  use PouetForm;
  function Validate($data)
  {
    return parent::Validate($data);
  }
  function Commit($data)
  {
    global $partyID;

    $this->LoadFromDB();

    $sql = array();
    if ($this->fields["download"])
      $sql["download"] = $data["download"];
    if ($this->fields["csdbID"])
      $sql["csdb"] = (int)$data["csdbID"];
    if ($this->fields["slengpungID"])
      $sql["slengpung"] = (int)$data["slengpungID"];
/*
    if ($this->fields["zxdemoID"])
      $sql["zxdemo"] = $data["zxdemoID"];
*/
    if ($this->fields["demozooID"])
      $sql["demozoo"] = (int)$data["demozooID"];
    if ($this->fields["artcity"])
      $sql["artcity"] = $data["artcity"];

    if ($sql)
    {
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
    }

    if (is_uploaded_file($_FILES["results"]["tmp_name"]))
    {
      move_uploaded_file_fake($_FILES["results"]["tmp_name"],get_local_partyresult_path($this->id,$this->year));
    }
    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();

    $this->links = SQLLib::selectRow(sprintf_esc("select * from partylinks where party = %d and year = %d",$this->id,$this->year));

    if ($this->links)
    {
      if ($this->links->download)
        unset($this->fields["download"]);
      if ($this->links->csdb)
        unset($this->fields["csdbID"]);
      if ($this->links->slengpung)
        unset($this->fields["slengpungID"]);
/*
      if ($this->links->zxdemo)
        unset($this->fields["zxdemoID"]);
*/
      if ($this->links->demozoo)
        unset($this->fields["demozooID"]);
      if ($this->links->artcity)
        unset($this->fields["artcity"]);
      if (file_exists(get_local_partyresult_path($this->id,$this->year)))
        unset($this->fields["results"]);
    }

    foreach($_POST as $k=>$v)
      if (@$this->fields[$k])
        $this->fields[$k]["value"] = $v;
  }
}

$box = new PouetBoxSubmitPartyEditionInfo( $_GET["which"], $_GET["when"] );
if (!$box->party)
{
  redirect("parties.php");
}

$TITLE = sprintf("submit links for a party edition: %s %04d",$box->party->name,$box->year);

$form = new PouetFormProcessor();

$form->SetSuccessURL( "party.php?which=".(int)$_GET["which"]."&when=".(int)$_GET["when"], true );

$form->Add( "partyInfo", $box );

if ($currentUser && $currentUser->CanSubmitItems())
  $form->Process();

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (get_login_id() && $box->prods)
{
  $form->Display();
}
else if (!$box->prods)
{
  $msg = new PouetBoxModalMessage( true );
  $msg->classes[] = "errorbox";
  $msg->title = "An error has occured:";
  $msg->message = "You cannot add stuff to a party that has no prods !";
  $msg->Render();
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
