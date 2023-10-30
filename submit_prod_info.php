<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-prod-submit.php");

if ($currentUser && !$currentUser->CanSubmitItems())
{
  redirect("prod.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxSubmitProdInfo extends PouetBoxSubmitProd
{
  public $prod;
  function __construct( $id )
  {
    parent::__construct();


    $this->prod = PouetProd::Spawn( $id );
    $a = array(&$this->prod);
    PouetCollectPlatforms( $a );

    $this->title = "submit things for this prod: "._html($this->prod->name);
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

    if( ($data["releaseDate_month"]&&$data["releaseDate_year"]) && ($data["releaseDate_month"]>date('m')&&$data["releaseDate_year"]>=date('Y')) ) {
      $errormessage[]="you can't submit a prod released in the future, sorry =)";
    }

    if (@$this->fields["partyID"] && @$this->fields["partyYear"])
    {
      if($data["partyYear"] && !$data["partyID"])
        $errormessage[] = "please either select a party AND a year, or neither !";
      if(($data["partyID"] && !$data["partyYear"]) && $data["partyID"] != NO_PARTY_ID)
        $errormessage[] = "please either select a party AND a year, or neither !";
      if($data["partyRank"] && !$data["partyID"])
        $errormessage[] = "please select a party before you select a ranking !";
    }

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
    if(is_uploaded_file($_FILES["nfofile"]["tmp_name"]))
    {
      if(filesize($_FILES["nfofile"]["tmp_name"]) > 32768) {
        $errormessage[]="the size of the infofile must not be greater than 32Kb";
      }
    }

    return $errormessage;
  }

  function Commit($data)
  {
    $this->LoadFromDB();

    $prodID = (int)$this->prod->id;

    $sql = array();

    if ($this->fields["releaseDate"])
    {
      if ($data["releaseDate_month"] && $data["releaseDate_year"] && checkdate( (int)$data["releaseDate_month"], 15, (int)$data["releaseDate_year"]) )
        $sql["releaseDate"] = sprintf("%04d-%02d-15",$data["releaseDate_year"],$data["releaseDate_month"]);
      else if ($data["releaseDate_year"])
        $sql["releaseDate"] = sprintf("%04d-00-15",$data["releaseDate_year"]);
      else
        $sql["releaseDate"] = null;
    }

    if ($this->fields["partyCompo"])
      $sql["party_compo"] = nullify($data["partyCompo"]);
    if ($this->fields["partyRank"])
      $sql["party_place"] = (int)$data["partyRank"];

    if ($sql)
      SQLLib::UpdateRow("prods",$sql,"id=".$prodID);

    if ($this->fields["screenshot"])
    {
      if(is_uploaded_file($_FILES["screenshot"]["tmp_name"]))
      {
        foreach( array( "jpg","gif","png" ) as $v )
          @unlink( get_local_screenshot_path( $prodID, $v ) );

        list($width,$height,$type) = GetImageSize($_FILES["screenshot"]["tmp_name"]);
        $extension = "_";
        switch($type) {
          case 1:$extension="gif";break;
          case 2:$extension="jpg";break;
          case 3:$extension="png";break;
        }
        move_uploaded_file_fake( $_FILES["screenshot"]["tmp_name"], get_local_screenshot_path( $prodID, $extension ) );

        $a = array();
        $a["prod"] = $prodID;
        $a["user"] = get_login_id();
        $a["added"] = date("Y-m-d H:i:s");
        SQLLib::InsertRow("screenshots",$a);
      }
    }
    if ($this->fields["nfofile"])
    {
      if(is_uploaded_file($_FILES["nfofile"]["tmp_name"]))
      {
        move_uploaded_file_fake( $_FILES["nfofile"]["tmp_name"], get_local_nfo_path( $prodID ) );

        $a = array();
        $a["prod"] = $prodID;
        $a["user"] = get_login_id();
        $a["added"] = date("Y-m-d H:i:s");
        SQLLib::InsertRow("nfos",$a);
      }
    }
    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();

    $prod = $this->prod;

    $a = array();
    unset($this->fields["name"]);
    unset($this->fields["download"]);

    unset($this->fields["group1"]);
    unset($this->fields["group2"]);
    unset($this->fields["group3"]);

    if ($prod->releaseDate)
      unset($this->fields["releaseDate"]);

    unset($this->fields["platform"]);
    unset($this->fields["type"]);

    unset($this->fields["partyID"]);
    unset($this->fields["partyYear"]);
    
    if ((!$prod->party || $prod->party->id == NO_PARTY_ID) || ($prod->placings && $prod->placings[0]->compo))
      unset($this->fields["partyCompo"]);
    if ((!$prod->party || $prod->party->id == NO_PARTY_ID) || ($prod->placings && $prod->placings[0]->ranking))
      unset($this->fields["partyRank"]);

    //unset($this->fields["sceneOrgID"]);
    unset($this->fields["zxdemoID"]);
    unset($this->fields["csdbID"]);
    unset($this->fields["demozooID"]);
    unset($this->fields["invitationParty"]);
    unset($this->fields["invitationYear"]);
    if ( file_exists( get_local_nfo_path( $prod->id ) ) )
      unset($this->fields["nfofile"]);
    if ( glob( get_local_screenshot_path( $prod->id, "*" ) ) )
      unset($this->fields["screenshot"]);
  }
}

$box = new PouetBoxSubmitProdInfo( $_GET["which"] );

$TITLE = "submit things for a prod: ".$box->prod->name;

if (!$box->prod)
{
  redirect("prodlist.php");
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "prod.php?which=".(int)$_GET["which"], true );

$form->Add( "prodInfo", $box );

if ($currentUser && $currentUser->CanSubmitItems())
  $form->Process();

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (get_login_id())
{
  $form->Display();

?>
<script>
document.observe("dom:loaded",function(){
  PrepareSubmitForm();
});
</script>
<?php

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
