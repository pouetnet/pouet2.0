<?php

class PouetBoxSubmitParty extends PouetBox
{
  public $formifier;
  public $fields;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_submitparty";
    $this->title = "submit a party!";
    $this->formifier = new Formifier();
    $this->fields = array();
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
      return array("Oh yeah, the party with no name, I remember that one!");
    }
    if ($data["website"])
    {
      $url = parse_url($data["website"]);
      if (($url["scheme"]!="http" && $url["scheme"]!="https") || strstr($data["website"],"://")===false)
        return array("please only websites with http or https links, kthx");
    }
    return array();
  }
  function Commit( $data )
  {
    $a = array();
    $a["name"] = trim($data["name"]);
    $a["web"] = $data["website"];
    $a["addedUser"] = get_login_id();
    $a["addedDate"] = date("Y-m-d H:i:s");
    $this->partyID = SQLLib::InsertRow("parties",$a);

    return array();
  }
  function GetInsertionID()
  {
    return $this->partyID;
  }

  function LoadFromDB()
  {
    global $PLATFORMS;
    $plat = array();
	  foreach($PLATFORMS as $k=>$v) $plat[$k] = $v["name"];
	  uasort($plat,"strcasecmp");

    $this->fields = array(
      "name"=>array(
        "name"=>"party name",
        "required"=>true,
        "infoAfter" => "only enter the party name, but <b>not</b> the year !<br/>eg. <b>Assembly</b> and not <strike><b>Assembly 1996</b></strike>",
      ),
      "website"=>array(
        "name"=>"website url",
        "type"=>"url",
      ),
    );
    foreach($_POST as $k=>$v)
      if ($this->fields[$k])
        $this->fields[$k]["value"] = $v;
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
?>
<script>
<!--
document.observe("dom:loaded",function(){
  $("name").observe("blur",function(ev){
    $("name").value = $("name").value.replace(/ <?=date("Y")?>$/,"");
  });
});
-->
</script>
<?php
  }
};

?>
