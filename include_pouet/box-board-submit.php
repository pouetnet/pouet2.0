<?php
class PouetBoxSubmitBoard extends PouetBox
{
  public $formifier;
  public $fields;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_submitboard";
    $this->title = "submit a board!";
    $this->formifier = new Formifier();
    $this->fields = array();
  }
  use PouetForm;
  function Validate( $data )
  {
    global $boardID,$currentUser;

    if (!$currentUser)
      return array("you have to be logged in !");

    if (!$currentUser->CanSubmitItems())
      return array("not allowed lol !");

    if (!trim($data["name"]))
    {
      return array("Oh yeah, the board with no name, I remember that one!");
    }
    return array();
  }
  function Commit( $data )
  {
    global $currentUser;
    
    $a = array();
    $a["name"] = trim($data["name"]);
    $a["sysop"] = trim($data["sysop"]);
    
    if( $data["started_year"] && $data["started_month"] && checkdate( (int)$data["started_month"], 15, (int)$data["started_year"]) )
      $a["started"] = sprintf("%04d-%02d-15",$data["started_year"],$data["started_month"]);
    else if ($data["started_year"])
      $a["started"] = sprintf("%04d-00-15",$data["started_year"]);

    if( $data["closed_year"] && $data["closed_month"] && checkdate( (int)$data["closed_month"], 15, (int)$data["closed_year"]) )
      $a["closed"] = sprintf("%04d-%02d-15",$data["closed_year"],$data["closed_month"]);
    else if ($data["closed_year"])
      $a["closed"] = sprintf("%04d-00-15",$data["closed_year"]);
    
    $a["phonenumber"] = trim($data["phonenumber"]);
    $a["telnetip"] = trim($data["telnetip"]);

    $a["addedUser"] = $currentUser->id;
    $a["addedDate"] = date("Y-m-d H:i:s");

    $this->boardID = SQLLib::InsertRow("boards",$a);

    $data["platform"] = array_unique($data["platform"]);
    foreach($data["platform"] as $k=>$v)
    {
      $a = array();
      $a["board"] = $this->boardID;
      $a["platform"] = $v;
      SQLLib::InsertRow("boards_platforms",$a);
    }
    return array();
  }
  function GetInsertionID()
  {
    return $this->boardID;
  }

  function LoadFromDB()
  {
    global $PLATFORMS;
    $plat = array();
	  foreach($PLATFORMS as $k=>$v) $plat[$k] = $v["name"];
	  uasort($plat,"strcasecmp");
  
    $this->fields = array(
      "name"=>array(
        "name"=>"board name",
        "required"=>true,
      ),
      "sysop"=>array(
        "name"=>"sysop name",
      ),
      "started"=>array(
        "name"=>"board inception date",
        "type"=>"dateMonth",
      ),
      "closed"=>array(
        "name"=>"board closure date",
        "type"=>"dateMonth",
      ),
      "platform"=>array(
        "name"=>"platforms",
        "type"=>"select",
        "multiple"=>true,
        "assoc"=>true,
        "fields"=>$plat,
        "info"=>"ctrl + click or cmd + click to select more than one !",
        "required"=>true,
      ),
      "phonenumber"=>array(
        "name"=>"phone number",
        "infoAfter"=>
          "follow this standard: <b>+countrycode-citycode-phonenumber</b> eg: <b>+7-095-391XXXX</b> for a moscow bbs<br />".
          "if it isnt demoscene related <b>DONT ADD IT</b><br />".
          "if it has illegal stuff on it and is still in service <b>DONT ADD IT</b><br /> or atleast ask the sysop first, or dont state the actual numbers, be safe before sorry.<br />".
          "on any cases unless you are/were the sysop, or have his/her permission, <b>dont disclose the last numbers</b>."
      ),
      "telnetip"=>array(
        "name"=>"telnet address",
      ),
    );
    foreach($_POST as $k=>$v)
      if (@$this->fields[$k])
        $this->fields[$k]["value"] = $v;
  }

  function Render()
  {
    global $boardID,$currentUser;

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

?>
