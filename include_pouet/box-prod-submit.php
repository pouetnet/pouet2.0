<?
class PouetBoxSubmitProd extends PouetBox 
{
  function PouetBoxSubmitProd() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_submitprod";
    $this->title = "submit a prod!";
    $this->formifier = new Formifier();
    $this->fields = array();
    
    $row = SQLLib::selectRow("DESC prods type");
    preg_match_all("/'([^']+)'/",$row->Type,$m);
    $this->types = $m[1];
    
    $row = SQLLib::selectRow("DESC prods partycompo");
    preg_match_all("/'([^']+)'/",$row->Type,$m);
    $this->compos = array("");
    $this->compos = array_merge($this->compos,$m[1]);
    
    $this->ranks = array(0=>"");
    $this->ranks[97] = "disqualified";
    $this->ranks[98] = "not applicable";
    $this->ranks[99] = "not shown";
    for ($x=1; $x<=96; $x++) $this->ranks[$x] = $x;
    
    $this->years = array("");
    for ($x=date("Y"); $x>=POUET_EARLIEST_YEAR; $x--) $this->years[$x] = $x;
  }

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

    if(!$data["download"])
    {
  	  $errormessage[]="no download link?!";
  	  return $errormessage;
  	}

    $myurl=parse_url($data["download"]);
    if(($myurl["scheme"]!="http")&&($myurl["scheme"]!="ftp")&&($myurl["scheme"]!="https"))
      $errormessage[] = "only http/https and ftp protocols are supported for the download link";
    if(strlen($myurl["host"])==0)
      $errormessage[] = "missing hostname in the download link";
    if(strstr($myurl["host"],"back2roots"))
      $errormessage[] = "back2roots does not allow download from outside, find another host please";
    if(strstr($myurl["host"],"intro-inferno"))
      $errormessage[] = "\"stop linking to intro-inferno, you turds :)\" /reed/";

    if(strstr($myurl["host"],"geocities"))
      $errormessage[] = "please get proper hosting (e.g. untergrund or scene.org) without traffic limits";
    if(strstr($myurl["host"],"docs.google"))
      $errormessage[] = "please get proper, permanent hosting";

    $shithosts = array(
      "rapidshare",
      "depositfiles",
      "megaupload",
      "filefactory",
      "sendspace",
      "netload",
      "mediafire",
      "megashare",
      "uploading.com",
      "mirrorcreator",
      "multiupload",
    );
    foreach ($shithosts as $v)
      if(strstr($myurl["host"],$v))
        $errormessage[] = "seriously, get better hosting";
      
    if(strstr($myurl["host"],"youtube") || strstr($myurl["host"],"youtu.be"))
      $errormessage[] = "FUCK YOUTUBE - BINARY OR GTFO";
      
    // ** apparently this is needed for csdb - still think its a bad idea
    //if(strstr($myurl["path"],".php") && !strstr($myurl["host"],"scene.org"))
    //  $errormessage[] = "please link to the file directly";

    if(strstr($myurl["path"],".txt"))
      $errormessage[] = "NO TEXTFILES.";

    if(strstr($myurl["host"],"untergrund.net"))
    {
      for ($x=1; $x<=5; $x++)
       if(strstr($myurl["host"],"ftp".$x.".untergrund.net"))
        $errormessage[] = "scamp says: link to ftp.untergrund.net not ftp".$x.".untergrund.net!!";
      if ($myurl["scheme"]=="http")
       $errormessage[] = "scamp says: no link to untergrund.net via http please!";
      if(strstr($myurl["host"],"www.untergrund.net"))
       $errormessage[] = "scamp says: godverdom!! link to ftp.untergrund.net instead!";
    }
    if(strstr($myurl["path"],"incoming"))
      $errormessage[] = "the file you submitted is in an incoming path, try to find a real path";
    if(strstr($myurl["host"],"scene.org") && strstr($myurl["query"],"incoming"))
      $errormessage[] = "the file you submitted is in an incoming path, try to find a real path";
    if( ((($myurl["port"])!=80) && (($myurl["port"])!=0)) && ((strlen($myurl["user"])>0) || (strlen($myurl["pass"])>0)) )
      $errormessage[] = "no private FTP please";
    if(!basename($myurl["path"]))
      $errormessage[] = "no file? no prod!";

    if( ($data["releaseDate_month"]&&$data["releaseDate_year"]) )
    {
      if ( ($data["releaseDate_month"]>date('m')&&$data["releaseDate_year"]==date('Y')) || ($data["releaseDate_year"]>date('Y')) ) 
      {
        $errormessage[]="you can't submit a prod released in the future, sorry =)";
      }
    }
    
    if(!count($data["type"])) {
      $errormessage[] = "you must select at least one type for this prod";
    }
    if(!count($data["platform"])) {
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
    $a["name"] = $data["name"];
    $a["download"] = $data["download"];
    
    $a["added"] = get_login_id();
    $a["quand"] = date("Y-m-d H:i:s");
    
    if( checkdate( $data["releaseDate_month"], 15, $data["releaseDate_year"]) )
      $a["date"] = sprintf("%04d-%02d-15",$data["releaseDate_year"],$data["releaseDate_month"]);
    else if ($data["releaseDate_year"])
      $a["date"] = sprintf("%04d-00-15",$data["releaseDate_year"]);
      
    $a["type"] = implode(",",$data["type"]);
        
    $groups = array();
    if ($data["group1"]) $groups[] = (int)$data["group1"];
    if ($data["group2"]) $groups[] = (int)$data["group2"];
    if ($data["group3"]) $groups[] = (int)$data["group3"];
    $groups = array_unique($groups);
    if (count($groups)) $a["group1"] = array_shift($groups);
    if (count($groups)) $a["group2"] = array_shift($groups);
    if (count($groups)) $a["group3"] = array_shift($groups);
    
    $a["csdb"] = $data["csdbID"];
    $a["sceneorg"] = $data["sceneOrgID"];
    $a["zxdemo"] = $data["zxdemoID"];
    $a["party"] = $data["partyID"];
    $a["party_year"] = $data["partyYear"];
    $a["partycompo"] = $data["partyCompo"];
    $a["party_place"] = $data["partyRank"];
    $a["invitation"] = $data["invitationParty"];
    $a["invitationyear"] = $data["invitationYear"];
    $a["boardID"] = $data["boardID"];
    
    global $prodID;
    $prodID = SQLLib::InsertRow("prods",$a);    
    
    $data["platform"] = array_unique($data["platform"]);
    foreach($data["platform"] as $k=>$v)
    {
      $a = array();
      $a["prod"] = $prodID;
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
      move_uploaded_file( $_FILES["screenshot"]["tmp_name"], get_local_screenshot_path( $prodID, $extension ) );

      $a = array();
      $a["prod"] = $prodID;
      $a["user"] = get_login_id();
      $a["added"] = date("Y-m-d H:i:s");
      SQLLib::InsertRow("screenshots",$a);    
    }    
    if(is_uploaded_file($_FILES["nfofile"]["tmp_name"])) 
    {
      move_uploaded_file( $_FILES["nfofile"]["tmp_name"], get_local_nfo_path( $prodID ) );

      $a = array();
      $a["prod"] = $prodID;
      $a["user"] = get_login_id();
      $a["added"] = date("Y-m-d H:i:s");
      SQLLib::InsertRow("nfos",$a);    
    }    
    
    @unlink("cache/pouetbox_latestadded.cache");
    @unlink("cache/pouetbox_latestreleased.cache");
    @unlink("cache/pouetbox_latestparties.cache");
    
    return array();
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
      ),
      "download"=>array(
        "type"=>"url",
        "name"=>"download url",
        "info"=>" ",
        "required"=>true,
      ),
      "releaseDate"=>array(
        "name"=>"release date",
        "type"=>"date",
      ),
      "type"=>array(
        "name"=>"type",
        "type"=>"select",
        "multiple"=>true,
        "fields"=>$this->types,
        "info"=>" ",
        "required"=>true,
      ),
      "platform"=>array(
        "name"=>"platform",
        "type"=>"select",
        "multiple"=>true,
        "assoc"=>true,
        "fields"=>$plat,
        "info"=>" ",
        "required"=>true,
      ),
      "csdbID"=>array(
        "name"=>"csdb ID",
      ),
      "sceneOrgID"=>array(
        "name"=>"scene.org ID",
      ),
      "zxdemoID"=>array(
        "name"=>"zxdemo ID",
      ),
      "partyID"=>array(
        "name"=>"party",
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
        "fields"=>$this->years,
      ),
      "boardID"=>array(
        "name"=>"bbs affiliation",
      ),
      "nfofile"=>array(
        "name"=>"infofile / file_id.diz",
        "type"=>"file",
      ),
      "screenshot"=>array(
        "name"=>"screenshot",
        "type"=>"file",
        "accept"=>"image/*",
      ),
    );
    if ($_POST)
    {
      foreach($_POST as $k=>$v)
        if ($this->fields[$k])
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

    $fields = array_select($this->fields,array("sceneOrgID","csdbID","zxdemoID","boardID"));
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
