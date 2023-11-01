<?php
class PouetBoxSubmitProd extends PouetBox
{
  public $formifier;
  public $fields;
  public $types;
  public $compos;
  public $ranks;
  public $years;
  public $yearsFuture;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_submitprod";
    $this->title = "submit a prod!";
    $this->formifier = new Formifier();
    $this->fields = array();

    $row = SQLLib::selectRow("DESC prods type");
    $this->types = enum2array($row->Type);

    global $COMPOTYPES;
    $this->compos = $COMPOTYPES;
    $this->compos[0] = "";
    asort($this->compos);

    $this->ranks = array(0=>"");
    $this->ranks[97] = "disqualified";
    $this->ranks[98] = "not applicable";
    $this->ranks[99] = "not shown";
    for ($x=1; $x<=96; $x++) $this->ranks[$x] = $x;

    $this->years = array("");
    for ($x=date("Y"); $x>=POUET_EARLIEST_YEAR; $x--) $this->years[$x] = $x;
    $this->yearsFuture = array("");
    for ($x=date("Y")+2; $x>=POUET_EARLIEST_YEAR; $x--) $this->yearsFuture[$x] = $x;
  }
  
  use PouetForm;

  function Validate( $data )
  {
    global $currentUser;
    $errormessage = array();

    if(!$currentUser)
    {
  	  $errormessage[]="you need to be logged in first.";
  	  return $errormessage;
  	}
    if (!$currentUser->CanSubmitItems())
    {
      $errormessage[] = "you there. please do not add prods.";
  	  return $errormessage;
  	}

    if(!$data["name"])
    {
  	  $errormessage[]="sorry, alien prophets already did a demo with no title!";
  	  return $errormessage;
  	}

    $e = validateDownloadLink( $data["download"] );
    if (count($e))
      $errormessage = array_merge($errormessage,$e);


    if( ($data["releaseDate_month"]&&$data["releaseDate_year"]) )
    {
      if ( ($data["releaseDate_month"]>date('m')&&$data["releaseDate_year"]==date('Y')) || ($data["releaseDate_year"]>date('Y')) )
      {
        $errormessage[]="you can't submit a prod released in the future, sorry =)";
      }
    }

    if(!count(@$data["type"]?:array())) {
      $errormessage[] = "you must select at least one type for this prod";
    }
    if(!count(@$data["platform"]?:array())) {
      $errormessage[] = "you must select at least one platform";
    }

    if($data["partyYear"] && !$data["partyID"])
      $errormessage[] = "please either select a party AND a year, or neither !";
    if(($data["partyID"] && !$data["partyYear"]) && $data["partyID"] != NO_PARTY_ID)
      $errormessage[] = "please either select a party AND a year, or neither !";
    if($data["partyRank"] && !$data["partyID"])
      $errormessage[] = "please select a party before you select a ranking !";

    if($data["invitationParty"] && !$data["invitationYear"])
      $errormessage[] = "please either select an invitation party AND a year, or neither !";

    $extension = "";
    if(is_uploaded_file($_FILES["screenshot"]["tmp_name"]))
    {
      list($width,$height,$type) = GetImageSize($_FILES["screenshot"]["tmp_name"]);
      if($type!=IMAGETYPE_GIF && $type!=IMAGETYPE_JPEG && $type!=IMAGETYPE_PNG) {
        $errormessage[]="the screenshot is not a valid .gif/jpg or .png file";
      }
      if($width > 400) {
        $errormessage[]="the width of the screenshot must not be greater than 400 pixels";
      }
      if($height > 300) {
        $errormessage[]="the height of the screenshot must not be greater than 300 pixels";
      }
      if(filesize($_FILES["screenshot"]["tmp_name"]) > 65536) {
        $errormessage[]="the size of the screenshot must not be greater than 64Kb";
      }
    }
    // check the .nfo
    if(is_uploaded_file($_FILES["nfofile"]["tmp_name"]))
    {
      if (!$currentUser->IsGloperator()) // gloperators are exempt from size limits
      {
        if(filesize($_FILES["nfofile"]["tmp_name"]) > 32768) {
          $errormessage[]="the size of the infofile must not be greater than 32Kb";
        }
      }
    }

    return $errormessage;
  }

  function Commit($data)
  {
    //////////////////////////////////////////////////////////////////
    // everything has been validated (..., the new album by BT!)

    $a = array();
    $a["name"] = trim($data["name"]);
    
    
    $url = trim($data["download"]);
    $parsedUrl = parse_url($url);
    if (strstr($parsedUrl["host"],"scene.org")!==false)
    {
      $sideload = new Sideload();
      $response = $sideload->Request("https://files.scene.org/api/resolve/","GET",array("url"=>$url));
      if ($response)
      {
        $responseJSON = json_decode($response,true);
        if ($responseJSON["success"] && $responseJSON["viewURL"])
        {
          $url = $responseJSON["viewURL"];
        }
      }
    }
    
    $a["download"] = $url;

    $a["addedUser"] = get_login_id();
    $a["addedDate"] = date("Y-m-d H:i:s");

    if( $data["releaseDate_year"] && $data["releaseDate_month"] && checkdate( (int)$data["releaseDate_month"], 15, (int)$data["releaseDate_year"]) )
      $a["releaseDate"] = sprintf("%04d-%02d-15",$data["releaseDate_year"],$data["releaseDate_month"]);
    else if ($data["releaseDate_year"])
      $a["releaseDate"] = sprintf("%04d-00-15",$data["releaseDate_year"]);

    $a["type"] = implode(",",$data["type"]);

    $groups = array();
    if ($data["group1"]) $groups[] = (int)$data["group1"];
    if ($data["group2"]) $groups[] = (int)$data["group2"];
    if ($data["group3"]) $groups[] = (int)$data["group3"];
    $groups = array_unique($groups);
    $a["group1"] = nullify( array_shift($groups) );
    $a["group2"] = nullify( array_shift($groups) );
    $a["group3"] = nullify( array_shift($groups) );

    $a["csdb"] = (int)$data["csdbID"];
    //$a["sceneorg"] = $data["sceneOrgID"];
    //$a["zxdemo"] = $data["zxdemoID"];
    $a["demozoo"] = (int)$data["demozooID"];
    $a["party"] = nullify($data["partyID"]);
    $a["party_year"] = (int)$data["partyYear"];
    $a["party_compo"] = nullify($data["partyCompo"]);
    $a["party_place"] = (int)$data["partyRank"];
    $a["invitation"] = nullify($data["invitationParty"]);
    $a["invitationyear"] = (int)$data["invitationYear"];
    $a["boardID"] = nullify($data["boardID"]);

    $this->prodID = SQLLib::InsertRow("prods",$a);

    $data["platform"] = array_unique($data["platform"]);
    foreach($data["platform"] as $k=>$v)
    {
      $a = array();
      $a["prod"] = $this->prodID;
      $a["platform"] = $v;
      SQLLib::InsertRow("prods_platforms",$a);
    }

    if(is_uploaded_file($_FILES["screenshot"]["tmp_name"]))
    {
      list($width,$height,$type) = GetImageSize($_FILES["screenshot"]["tmp_name"]);
      $extension = "_";
      switch($type) {
        case 1:$extension="gif";break;
        case 2:$extension="jpg";break;
        case 3:$extension="png";break;
      }
      move_uploaded_file_fake( $_FILES["screenshot"]["tmp_name"], get_local_screenshot_path( $this->prodID, $extension ) );

      $a = array();
      $a["prod"] = $this->prodID;
      $a["user"] = get_login_id();
      $a["added"] = date("Y-m-d H:i:s");
      SQLLib::InsertRow("screenshots",$a);
    }
    if(is_uploaded_file($_FILES["nfofile"]["tmp_name"]))
    {
      move_uploaded_file_fake( $_FILES["nfofile"]["tmp_name"], get_local_nfo_path( $this->prodID ) );

      $a = array();
      $a["prod"] = $this->prodID;
      $a["user"] = get_login_id();
      $a["added"] = date("Y-m-d H:i:s");
      SQLLib::InsertRow("nfos",$a);
    }

    flush_cache("pouetbox_latestadded.cache");
    flush_cache("pouetbox_latestreleased.cache");
    flush_cache("pouetbox_latestparties.cache");

    return array();
  }

  function GetInsertionID()
  {
    return $this->prodID;
  }

  function LoadFromDB()
  {
    global $PLATFORMS;
    $plat = array();
	  foreach($PLATFORMS as $k=>$v) $plat[$k] = $v["name"];
	  uasort($plat,"strcasecmp");

    $this->fields = array(
      "name"=>array(
        "name"=>"prod name / title",
        "info"=>" ",
        "required"=>true,
      ),
      "group1"=>array(
        "name"=>"group 1",
      ),
      "group2"=>array(
        "name"=>"group 2",
      ),
      "group3"=>array(
        "name"=>"group 3",
        "infoAfter"=>"if the group is missing from the list, add it <a href='submit_group.php' target='_blank'>here</a> !",
      ),
      "download"=>array(
        "type"=>"url",
        "name"=>"download url",
        "infoAfter"=>"<b>important !</b><br/>this has to be a link to a downloadable file, not to a website or a video version !".
        	" ad-ridden \"one-click\" hosting links will be dealt with extreme prejudice -".
        	" if it's not a direct link to the file on the first click, it will get deleted !".
        	" (scene.org links are an exception - <a href='http://www.pouet.net/faq.php#faq37'>read the faq</a> if you're confused)",
        "info"=>" ",
        "required"=>true,
        "maxlength"=>256,
      ),
      "releaseDate"=>array(
        "name"=>"release date",
        "type"=>"dateMonth",
      ),
      "type"=>array(
        "name"=>"type",
        "type"=>"select",
        "multiple"=>true,
        "fields"=>$this->types,
        "info"=>"ctrl + click or cmd + click to select more than one !",
        "required"=>true,
      ),
      "platform"=>array(
        "name"=>"platform",
        "type"=>"select",
        "multiple"=>true,
        "assoc"=>true,
        "fields"=>$plat,
        "info"=>"ctrl + click or cmd + click to select more than one !",
        "required"=>true,
      ),
      "csdbID"=>array(
        "name"=>"csdb ID",
        "type"=>"number",
      ),
      /*
      "sceneOrgID"=>array(
        "name"=>"scene.org ID",
      ),
      */
      "demozooID"=>array(
        "name"=>"demozoo ID",
        "type"=>"number",
      ),
      "partyID"=>array(
        "name"=>"party",
        "infoAfter"=>"if the party is missing from the list, add it <a href='submit_party.php' target='_blank'>here</a> !",
      ),
      "partyYear"=>array(
        "name"=>"party year",
        "type"=>"select",
        "fields"=>$this->years,
      ),
      "partyCompo"=>array(
        "name"=>"party compo",
        "type"=>"select",
        "fields"=>$this->compos,
        "assoc"=>true,
      ),
      "partyRank"=>array(
        "name"=>"party rank",
        "type"=>"select",
        "assoc"=>true,
        "fields"=>$this->ranks,
      ),
      "invitationParty"=>array(
        "name"=>"invitation for party",
      ),
      "invitationYear"=>array(
        "name"=>"invitation year",
        "type"=>"select",
        "fields"=>$this->yearsFuture,
      ),
      "boardID"=>array(
        "name"=>"bbs affiliation",
      ),
      "nfofile"=>array(
        "name"=>"infofile / file_id.diz",
        "type"=>"file",
        "info"=>"(maximum 32kb)",
      ),
      "screenshot"=>array(
        "name"=>"screenshot",
        "type"=>"file",
        "accept"=>"image/*",
        "info"=>"(maximum 400x300 pixels, and 64kb)",
      ),
    );
    if ($_POST)
    {
      foreach($_POST as $k=>$v)
        if (@$this->fields[$k])
          $this->fields[$k]["value"] = $v;
      $this->fields["releaseDate"]["value"] = sprintf("%04d-%02d-15",$_POST["releaseDate_year"],$_POST["releaseDate_month"]);
    }
  }

  function Render()
  {
    global $currentUser;

    if (!$currentUser)
      return;

    if (!$currentUser->CanSubmitItems())
      return;

    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";

    echo "  <h2>".$this->title."</h2>\n";
    $fields = array_select($this->fields,array("name"));
    if ($fields)
    {
      echo "  <div class='content'>\n";
      $this->formifier->RenderForm( $fields );
      echo "  </div>\n";
    }

    $fields = array_select($this->fields,array("group1","group2","group3"));
    if ($fields)
    {
      echo "  <h2 id='groups'>groups</h2>\n";
      echo "  <div class='content'>\n";
      $this->formifier->RenderForm( $fields );
      echo "  </div>\n";
    }

    $fields = array_select($this->fields,array("download","releaseDate","platform","type"));
    if ($fields)
    {
      echo "  <h2 id='basicinfo'>basic info</h2>\n";
      echo "  <div class='content'>\n";
      $this->formifier->RenderForm( $fields );
      echo "  </div>\n";
    }

    $fields = array_select($this->fields,array("partyID","partyYear","partyCompo","partyRank","invitationParty","invitationYear"));
    if ($fields)
    {
      echo "  <h2 id='partyinfo'>party info</h2>\n";
      echo "  <div class='content'>\n";
      $this->formifier->RenderForm( $fields );
      echo "  </div>\n";
    }

    $fields = array_select($this->fields,array("csdbID","demozooID","boardID"));
    if ($fields)
    {
      echo "  <h2 id='othersites'>other sites</h2>\n";
      echo "  <div class='content'>\n";
      $this->formifier->RenderForm( $fields );
      echo "  </div>\n";
    }

    $fields = array_select($this->fields,array("nfofile","screenshot"));
    if ($fields)
    {
      echo "  <h2 id='files'>files</h2>\n";
      echo "  <div class='content'>\n";
      $this->formifier->RenderForm( $fields );
      echo "  </div>\n";
    }

    echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};
?>
