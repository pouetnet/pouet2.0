<?

class PouetBoxSubmitPartyEdition extends PouetBox 
{
  function PouetBoxSubmitPartyEdition() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_submitpartyedition";
    $this->title = "submit a party edition!";
    $this->formifier = new Formifier();
    $this->fields = array();
  }
  
  function Validate( $data )
  {
    global $currentUser;
    
    if (!$currentUser)
      return array("you have to be logged in !");
    
    if (!$currentUser->CanSubmitItems())
      return array("not allowed lol !");
    
    if ($data["download"])
    {
      $url = parse_url($data["download"]);
      if (($url["scheme"]!="http" && $url["scheme"]!="ftp" && $url["scheme"]!="https") || strstr($data["download"],"://")===false)
        return array("please only websites with ftp, http or https links, kthx");
    }
    if ($data["artcity"])
    {
      if (preg_match("/[^a-zA-Z0-9\,]/",$data["artcity"]))
        return array("that's not a valid artcity tag-collection!");
    }
    return array();
  }
  function Commit( $data )
  {
    $a = array();
    $a["download"] = trim($data["download"]);
    $a["csdb"] = $data["csdbID"];
    $a["zxdemo"] = $data["zxdemoID"];
    $a["slengpung"] = $data["slengpungID"];
    $a["artcity"] = $data["artcity"];
    SQLLib::InsertRow("partylinks",$a);
    
    return array();
  }
  function LoadFromDB()
  {
    global $PLATFORMS;
    $plat = array();
	  foreach($PLATFORMS as $k=>$v) $plat[$k] = $v["name"];
	  uasort($plat,"strcasecmp");
  
    $this->fields = array(
      "download"=>array(
        "name"=>"download directory",
        "type"=>"url",
      ),
      "csdbID"=>array(
        "name"=>"csdb id",
      ),
      "zxdemoID"=>array(
        "name"=>"zxdemo id",
      ),
      "slengpungID"=>array(
        "name"=>"slengpung id",
      ),
      "artcity"=>array(
        "name"=>"artcity tags",
        "info"=>"normally this is in a \"partyname,partyyear\" format, such as \"chaos constructions,2005\"",
      ),
      "results"=>array(
        "name"=>"results file",
        "type"=>"file",
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
  }
};

?>