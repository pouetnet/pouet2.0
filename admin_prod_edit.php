<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-prod-submit.php");
require_once("include_pouet/pouet-box-editbase.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("prod.php?which=".(int)$_GET["which"]);
  exit();
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProd extends PouetBoxSubmitProd
{
  public $id;
  public $prod;
  function __construct( $id )
  {
    parent::__construct();

    $this->id = (int)$id;

    $this->prod = PouetProd::Spawn( $this->id );
    if (!$this->prod) return;
    $a = array(&$this->prod);
    PouetCollectPlatforms( $a );

    $this->formifier->canDeleteFiles = true;
    
    $this->title = "edit this prod: ".$this->prod->RenderLink();
  }
  use PouetForm;
  function Commit($data)
  {
    //die("almost there");
    $a = array();
    $a["name"] = trim($data["name"]);
    $a["download"] = trim($data["download"]);

    if ($data["releaseDate_month"] && $data["releaseDate_year"] && checkdate( (int)$data["releaseDate_month"], 15, (int)$data["releaseDate_year"]) )
      $a["releaseDate"] = sprintf("%04d-%02d-15",$data["releaseDate_year"],$data["releaseDate_month"]);
    else if ($data["releaseDate_year"])
      $a["releaseDate"] = sprintf("%04d-00-15",$data["releaseDate_year"]);
    else
      $a["releaseDate"] = null;

    $a["type"] = implode(",",$data["type"]);

    $groups = array();
    if ($data["group1"]) $groups[] = (int)$data["group1"];
    if ($data["group2"]) $groups[] = (int)$data["group2"];
    if ($data["group3"]) $groups[] = (int)$data["group3"];
    $groups = array_unique($groups);
    if (count($groups)) $a["group1"] = array_shift($groups); else $a["group1"] = null;
    if (count($groups)) $a["group2"] = array_shift($groups); else $a["group2"] = null;
    if (count($groups)) $a["group3"] = array_shift($groups); else $a["group3"] = null;

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
    global $prodID;
    SQLLib::UpdateRow("prods",$a,"id=".(int)$this->id);

    $data["platform"] = array_unique($data["platform"]);
    SQLLib::Query(sprintf_esc("delete from prods_platforms where prod = %d",(int)$this->id));
    foreach($data["platform"] as $v)
    {
      $a = array();
      $a["prod"] = (int)$this->id;
      $a["platform"] = $v;
      SQLLib::InsertRow("prods_platforms",$a);
    }

    if (@$data["screenshot_delete"])
    {
      SQLLib::Query(sprintf_esc("delete from screenshots where prod = %d",$this->id));
      foreach( array( "jpg","gif","png" ) as $v )
        @unlink( get_local_screenshot_path( (int)$this->id, $v ) );
    }
    else
    {
      if(is_uploaded_file($_FILES["screenshot"]["tmp_name"]))
      {
        foreach( array( "jpg","gif","png" ) as $v )
          @unlink( get_local_screenshot_path( (int)$this->id, $v ) );
  
        list($width,$height,$type) = GetImageSize($_FILES["screenshot"]["tmp_name"]);
        $extension = "_";
        switch($type) {
          case 1:$extension="gif";break;
          case 2:$extension="jpg";break;
          case 3:$extension="png";break;
        }
        if ($extension != "_")
          move_uploaded_file_fake( $_FILES["screenshot"]["tmp_name"], get_local_screenshot_path( (int)$this->id, $extension ) );
      }
    }    
    
    if (@$data["nfofile_delete"])
    {
      SQLLib::Query(sprintf_esc("delete from nfos where prod = %d",$this->id));
      unlink( get_local_nfo_path( (int)$this->id ) );
    }
    else
    {
      if(is_uploaded_file($_FILES["nfofile"]["tmp_name"]))
      {
        move_uploaded_file_fake( $_FILES["nfofile"]["tmp_name"], get_local_nfo_path( (int)$this->id ) );
      }
    }

    gloperator_log( "prod", (int)$this->id, "prod_edit" );
    
    SQLLib::Query(sprintf_esc("delete from prods_linkcheck where prodID = %d",$this->id));
    
    $prodID = $this->id;
    $partyID = $a["party"];
    flush_cache("pouetbox_latestadded.cache",function($i)use($prodID){ return $i->id == $prodID; } );
    flush_cache("pouetbox_latestreleased.cache",function($i)use($prodID){ return $i->id == $prodID; } );
    flush_cache("pouetbox_latestparties.cache",function($i)use($partyID){ return $i->id == $partyID; } );

    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();

    $prod = $this->prod;

    $a = array();
    $this->fields["name"]["value"] = $prod->name;
    $this->fields["download"]["value"] = $prod->download;

    $n = 1;
    foreach($prod->groups as $g)
      $this->fields["group".$n++]["value"] = $g->id;

    $this->fields["releaseDate"]["value"] = $prod->releaseDate;

    $this->fields["platform"]["value"] = array_keys($prod->platforms);
    $this->fields["type"]["value"] = $prod->types;

    if (count($prod->placings) > 0)
    {
      $this->fields["partyID"]["value"] = $prod->placings[0]->party->id;
      $this->fields["partyYear"]["value"] = $prod->placings[0]->year;
      $this->fields["partyCompo"]["value"] = $prod->placings[0]->compo;
      $this->fields["partyRank"]["value"] = $prod->placings[0]->ranking;
    }

    //$this->fields["sceneOrgID"]["value"] = $prod->sceneorg;
    $this->fields["demozooID"]["value"] = $prod->demozoo;
    $this->fields["csdbID"]["value"] = $prod->csdb;
    $this->fields["boardID"]["value"] = $prod->boardID;
    $this->fields["invitationParty"]["value"] = $prod->invitation;
    $this->fields["invitationYear"]["value"] = $prod->invitationyear;

  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminDeleteProd extends PouetBox
{
  public $prod;
  public $checkString;
  function __construct( $prod )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_proddelete";

    $this->classes[] = "errorbox";

    $this->prod = $prod;

    global $verificationStrings;
    $this->checkString = $verificationStrings[ array_rand($verificationStrings) ];

    $this->title = "delete this prod: ".$this->prod->RenderLink();
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
    $this->prod->Delete();
    return array();
  }
  function RenderBody()
  {
    echo "<div class='content'/>";
    echo "  <p>To make sure you want to delete <b>this</b> prod, type \"".$this->checkString."\" here:</p>";
    echo "  <input name='checkOrig' type='hidden' value='"._html($this->checkString)."'/>";
    echo "  <input id='check' name='check' autocomplete='no'/>";
    echo "</div>";
    echo "<div class='foot'/>";
    echo "  <input type='submit' value='Submit' />";
    echo "</div>";
    ?>
<script>
document.observe("dom:loaded",function(){
  $("pouetbox_proddelete").up("form").observe("submit",function(e){
    if ($F("check") != "<?=_js($this->checkString)?>")
    {
      alert("Enter the verification string!");
      e.stop();
      return;
    }
    if (!confirm("ARE YOU REALLY SURE YOU WANT TO DELETE \"<?=_js($this->prod->name)?>\"?!"))
      e.stop();
  });
});
</script>
    <?php
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdAwards extends PouetBoxEditConnectionsBase
{
  public $categories;
  public $prod;
  public $types;
  public static $slug = "Awards";

  function __construct( $prod )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_prodeditprodawards";
    $this->prod = $prod;
    $this->id = $prod->id;
    $this->title = "prod awards";

    $this->data = SQLLib::SelectRows(sprintf_esc("select * from awards where prodID = %d",$this->prod->id));
    $this->headers = array("series / category","result");
    
    $row = SQLLib::selectRow("DESC awards awardType");
    $this->types = enum2array($row->Type);

    global $AWARDS_CATEGORIES;
    $this->categories = array();
    foreach($AWARDS_CATEGORIES as $k=>$v)
    {
      $this->categories[$k] = sprintf("%s - %s",$v->series,$v->category);
    }
  }
  use PouetForm;
  function Commit($data)
  {
    if (@$data["delAwards"])
    {
      SQLLib::Query("delete from awards where id=".(int)$data["delAwards"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_awards_delete" );
      return array();
    }

    $a = array();
    $a["awardType"] = $data["awardType"];
    $a["categoryID"] = $data["awardCategory"];
    if (@$data["editAwardsID"])
    {
      SQLLib::UpdateRow("awards",$a,"id=".(int)$data["editAwardsID"]);
      $a["id"] = $data["editAwardsID"];

      gloperator_log( "prod", (int)$this->prod->id, "prod_awards_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["prodid"] = $this->prod->id;
      $a["id"] = SQLLib::InsertRow("awards",$a);

      gloperator_log( "prod", (int)$this->prod->id, "prod_awards_add", array("id"=>$a["id"]) );
    }
    if (@$data["partial"])
    {
      $this->RenderNormalRow(toObject($a));
      $this->RenderNormalRowEnd(toObject($a));
      exit();
    }
    return array();
  }
  function RenderEditRow($row = null)
  {
    echo "    <td><select name='awardCategory' class='awardCategory'>\n";
    foreach($this->categories as $k=>$v)
      printf("<option value='%d'%s>%s</option>",$k,($row&&$row->categoryID==$k)?" selected='selected'":"",_html($v));
    echo "</select></td>\n";
    echo "    <td><select name='awardType' class='awardType'>\n";
    foreach($this->types as $v)
      printf("<option%s>%s</option>",($row&&$row->awardType==$v)?" selected='selected'":"",_html($v));
    echo "</select></td>\n";
  }
  function RenderNormalRow($v)
  {
    global $AWARDS_CATEGORIES;
    
    $category = $AWARDS_CATEGORIES[$v->categoryID];
    echo "    <td>"._html($category->series." - ".$category->category)."</td>\n";
    echo "    <td>"._html($v->awardType)."</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodawards"), "prodAwards", {
  } );
});
//-->
</script>
<?php
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdLinks extends PouetBoxEditConnectionsBase
{
  public $prod;
  public static $slug = "Link";
  function __construct( $prod )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_prodeditprodlinks";
    $this->prod = $prod;
    $this->id = $prod->id;
    $this->title = "additional links";

    $this->headers = array("type","link");
    $this->data = SQLLib::SelectRows(sprintf_esc("select * from downloadlinks where prod = %d",$this->prod->id));
  }
  use PouetForm;
  function Commit($data)
  {
    if (@$data["delLink"])
    {
      SQLLib::Query("delete from downloadlinks where id=".(int)$data["delLink"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_link_del" );
      return array();
    }

    $a = array();
    $a["type"] = $data["type"];
    $a["link"] = $data["link"];
    if (@$data["editLinkID"])
    {
      SQLLib::UpdateRow("downloadlinks",$a,"id=".(int)$data["editLinkID"]);
      $a["id"] = $data["editLinkID"];
      gloperator_log( "prod", (int)$this->prod->id, "prod_link_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["prod"] = $this->prod->id;
      $a["id"] = SQLLib::InsertRow("downloadlinks",$a);
      gloperator_log( "prod", (int)$this->prod->id, "prod_link_add", array("id"=>$a["id"]) );
    }
    if (@$data["partial"])
    {
      $this->RenderNormalRow(toObject($a));
      $this->RenderNormalRowEnd(toObject($a));
      exit();
    }
    return array();
  }
  function RenderEditRow($row = null)
  {
    echo "    <td><input name='type' value='"._html($row?$row->type:"")."'/></td>\n";
    echo "    <td><input name='link' value='"._html($row?$row->link:"")."' type='url'/></td>\n";
  }
  function RenderNormalRow($v)
  {
    echo "    <td>"._html($v->type)."</td>\n";
    echo "    <td>"._html($v->link)."</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodlinks"), "prodLink" );
});
//-->
</script>
<?php
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdParties extends PouetBoxEditConnectionsBase
{
  public $compos;
  public $prod;
  public $ranks;
  public $years;
  public static $slug = "Party";
  function __construct( $prod )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_prodeditprodparties";
    $this->prod = $prod;
    $this->id = $prod->id;
    $this->title = "additional parties";

    $this->headers = array("party","year","compo","place");

    $s = new BM_Query();
    $s->AddField("prodotherparty.id");
    $s->AddField("prodotherparty.party_compo");
    $s->AddField("prodotherparty.party_place");
    $s->AddField("prodotherparty.party_year");
    $s->AddTable("prodotherparty");
    $s->attach(array("prodotherparty"=>"party"),array("parties as party"=>"id"));
    $s->AddWhere(sprintf_esc("prod=%d",$this->prod->id));
    $this->data = $s->perform();

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

  }
  use PouetForm;
  function Commit($data)
  {
    if (@$data["delParty"])
    {
      SQLLib::Query("delete from prodotherparty where id=".(int)$data["delParty"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_party_del" );
      return array();
    }

    $a = array();
    $a["party"] = $data["partyID"];
    $a["party_year"] = $data["partyYear"];
    $a["party_place"] = $data["partyPlace"];
    $a["party_compo"] = nullify($data["partyCompo"]);
    if (@$data["editPartyID"])
    {
      SQLLib::UpdateRow("prodotherparty",$a,"id=".(int)$data["editPartyID"]);
      $a["id"] = $data["editPartyID"];
      gloperator_log( "prod", (int)$this->prod->id, "prod_party_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["prod"] = $this->prod->id;
      $a["id"] = SQLLib::InsertRow("prodotherparty",$a);
      gloperator_log( "prod", (int)$this->prod->id, "prod_party_add", array("id"=>$a["id"]) );
    }
    if (@$data["partial"])
    {
      $o = toObject($a);
      $o->party = PouetParty::Spawn($a["party"]);
      $this->RenderNormalRow($o);
      $this->RenderNormalRowEnd($o);
      exit();
    }
    return array();
  }
  function RenderEditRow($row = null)
  {
    echo "    <td><input name='partyID' value='"._html($row&&$row->party?$row->party->id:"")."' class='partyID'/></td>\n";

    echo "    <td><select name='partyYear'>";
    foreach($this->years as $k=>$v)
      printf("<option value='%s'%s>%s</option>",_html($k),($row && $k == $row->party_year) ? " selected='selected'" : "",_html($v));
    echo "</select></td>\n";

    echo "    <td><select name='partyCompo'>";
    foreach($this->compos as $k=>$v)
      printf("<option value='%s'%s>%s</option>",_html($k),($row && $k == $row->party_compo) ? " selected='selected'" : "",_html($v));
    echo "</select></td>\n";

    echo "    <td><select name='partyPlace'>";
    foreach($this->ranks as $k=>$v)
      printf("<option value='%s'%s>%s</option>",_html($k),($row && $k == $row->party_place) ? " selected='selected'" : "",_html($v));
    echo "</select></td>\n";

  }
  function RenderNormalRow($v)
  {
    global $COMPOTYPES;
    echo "    <td>"._html($v->party->name)."</td>\n";
    echo "    <td>"._html($v->party_year)."</td>\n";
    echo "    <td>"._html($COMPOTYPES[$v->party_compo])."</td>\n";
    echo "    <td>"._html($v->party_place)."</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodparties"), "prodParty", {
    onRowLoad: function(tr){
      new Autocompleter(tr.down(".partyID"), {"dataUrl":"./ajax_parties.php"});
    }
  } );
});
//-->
</script>
<?php
  }
}


///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdCredits extends PouetBoxEditConnectionsBase
{
  public $prod;
  public static $slug = "Credit";
  function __construct( $prod )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_prodeditprodcredits";
    $this->prod = $prod;
    $this->id = $prod->id;
    $this->title = "credits";

    $this->headers = array("user","role");

    $s = new BM_Query();
    $s->AddTable("credits");
    $s->AddField("credits.id");
    $s->AddField("credits.role");
    $s->AddWhere(sprintf("credits.prodID = %d",$this->prod->id));
    $s->Attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $this->data = $s->perform();
  }
  use PouetForm;
  function Commit($data)
  {
    if (@$data["delCredit"])
    {
      SQLLib::Query("delete from credits where id=".(int)$data["delCredit"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_credits_del" );
      return array();
    }

    $a = array();
    $a["userID"] = $data["userID"];
    $a["role"] = $data["role"];
    if (@$data["editCreditID"])
    {
      SQLLib::UpdateRow("credits",$a,"id=".(int)$data["editCreditID"]);
      $a["id"] = $data["editCreditID"];
      gloperator_log( "prod", (int)$this->prod->id, "prod_credits_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["prodID"] = $this->prod->id;
      $a["id"] = SQLLib::InsertRow("credits",$a);
      gloperator_log( "prod", (int)$this->prod->id, "prod_credits_add", array("id"=>$a["id"]) );
    }
    if (@$data["partial"])
    {
      $o = toObject($a);
      $o->user = PouetUser::Spawn($a["userID"]);
      $this->RenderNormalRow($o);
      $this->RenderNormalRowEnd($o);
      exit();
    }
    return array();
  }
  function RenderEditRow($row = null)
  {
    echo "    <td><input name='userID' value='"._html($row&&$row->user?$row->user->id:"")."' class='userID'/></td>\n";
    echo "    <td><input name='role' value='"._html($row?$row->role:"")."' class='role'/></td>\n";
  }
  function RenderNormalRow($v)
  {
    echo "    <td>".($v->user ? $v->user->PrintLinkedAvatar()." ".$v->user->PrintLinkedName() : "")."</td>\n";
    echo "    <td>"._html($v->role)."</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodcredits"), "prodCredit", {
    onRowLoad: function(tr){
      new Autocompleter(tr.down(".userID"), {
        "dataUrl":"./ajax_users.php",
        "processRow": function(item) {
          return "<img class='avatar' src='<?=POUET_CONTENT_URL?>avatars/" + item.avatar.escapeHTML() + "'/> " + item.name.escapeHTML() + " <span class='glops'>" + item.glops + " glöps</span>";
        }
      });
    }
  } );
});
//-->
</script>
<?php
  }
}


///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdAffil extends PouetBoxEditConnectionsBase
{
  public $prod;
  public static $slug = "Affil";
  function __construct( $prod )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_prodeditprodaffil";
    $this->prod = $prod;
    $this->id = $prod->id;
    $this->title = "related prods";

    $this->headers = array("relation","prod");

    $s = new BM_Query();
    $s->AddField("affiliatedprods.id");
    $s->AddField("affiliatedprods.type");
    $s->AddTable("affiliatedprods");
    $s->attach(array("affiliatedprods"=>"original"),array("prods as prodOriginal"=>"id"));
    $s->attach(array("affiliatedprods"=>"derivative"),array("prods as prodDerivative"=>"id"));
    $s->AddWhere(sprintf_esc("original=%d or derivative=%d",$this->prod->id,$this->prod->id));
    $this->data = $s->perform();
  }
  use PouetForm;
  function Commit($data)
  {
    if (@$data["delAffil"])
    {
      SQLLib::Query("delete from affiliatedprods where id=".(int)$data["delAffil"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_rel_del" );
      return array();
    }

    list($direction,$type) = explode(":",$data["type"],2);
    $a = array();
    $a["type"] = $type;
    $a["original"]   = $direction == "o" ? $this->prod->id : $data["prod"];
    $a["derivative"] = $direction == "d" ? $this->prod->id : $data["prod"];
    if (@$data["editAffilID"])
    {
      SQLLib::UpdateRow("affiliatedprods",$a,"id=".(int)$data["editAffilID"]);
      $a["id"] = $data["editAffilID"];
      gloperator_log( "prod", (int)$this->prod->id, "prod_rel_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["id"] = SQLLib::InsertRow("affiliatedprods",$a);
      gloperator_log( "prod", (int)$this->prod->id, "prod_rel_add", array("id"=>$a["id"]) );
    }
    if (@$data["partial"])
    {
      $o = toObject($a);
      $o->prodOriginal   = PouetProd::Spawn($a["original"]);
      $o->prodDerivative = PouetProd::Spawn($a["derivative"]);
      $this->RenderNormalRow($o);
      $this->RenderNormalRowEnd($o);
      exit();
    }

    return array();
  }
  function RenderEditRow($row = null)
  {
    global $AFFILIATIONS_ORIGINAL;
    global $AFFILIATIONS_INVERSE;

    $a = ($row && $this->prod->id == $row->prodOriginal->id ? $AFFILIATIONS_ORIGINAL : $AFFILIATIONS_INVERSE);

    //echo "    <td><input name='type' value='"._html(($this->prod->id == $row->prodOriginal->id ? "o" : "d").":".$row->type)."'/></td>\n";
    echo "<td><select name='type'>";
    foreach($AFFILIATIONS_ORIGINAL as $k=>$v)
      printf("<option value='o:%s'%s>%s</option>",$k,($row && $row->prodOriginal && $this->prod->id == $row->prodOriginal->id && $k == $row->type) ? " selected='selected'" : "",$v);
    foreach($AFFILIATIONS_INVERSE as $k=>$v)
      printf("<option value='d:%s'%s>%s</option>",$k,($row && $row->prodDerivative && $this->prod->id == $row->prodDerivative->id && $k == $row->type) ? " selected='selected'" : "",$v);
    echo "</select></td>\n";

    echo "    <td><input name='prod' value='"._html( $row && $row->prodOriginal && $row->prodDerivative ? ($this->prod->id == $row->prodOriginal->id ? $row->prodDerivative->id : $row->prodOriginal->id) : "")."' class='prodID'/></td>\n";
  }
  function RenderNormalRow($v)
  {
    global $AFFILIATIONS_ORIGINAL;
    global $AFFILIATIONS_INVERSE;

    $a = ($this->prod->id == $v->prodOriginal->id ? $AFFILIATIONS_ORIGINAL : $AFFILIATIONS_INVERSE);
    $prod = ($this->prod->id == $v->prodOriginal->id ? $v->prodDerivative : $v->prodOriginal);
    echo "    <td>"._html($a[$v->type])."</td>\n";
    echo "    <td>".($prod ? $prod->RenderLink() : "")."</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodaffil"), "prodAffil", {
    onRowLoad: function(tr){
      new Autocompleter(tr.down(".prodID"), {
        "dataUrl":"./ajax_prods.php",
        "processRow": function(item) {
          var s = item.name.escapeHTML();
          if (item.groupName) s += " <small class='group'>" + item.groupName.escapeHTML() + "</small>";
          return s;
        }
      });
    }
  } );
});
//-->
</script>
<?php
  }
}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

$boxen = array(
  "PouetBoxAdminEditProdLinks",
  "PouetBoxAdminEditProdCredits",
  "PouetBoxAdminEditProdParties",
  "PouetBoxAdminEditProdAwards",
  "PouetBoxAdminEditProdAffil",
);
if(@$_GET["partial"] && $currentUser && $currentUser->CanEditItems())
{
  // ajax responses
  $prod = new stdClass();
  $prod->id = $_GET["which"];
  foreach($boxen as $class)
  {
    $box = new $class( $prod );
    $box->RenderPartialResponse();
  }
  exit();
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "prod.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditProd( $_GET["which"] );
if ($box->prod)
{
  $form->Add( "prod", $box );
  foreach($boxen as $class)
    $form->Add( "prod" . $class::$slug, new $class($box->prod) );
  if ($currentUser && $currentUser->CanDeleteItems())
    $form->Add( "prodDelete", new PouetBoxAdminDeleteProd($box->prod) );
}
if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit a prod: ".$box->prod->name;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if ($box->prod)
{
  if (get_login_id())
  {
    $form->Display();
  ?>
  <script>
  document.observe("dom:loaded",function(){
    if (!$("row_csdbID")) return;
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
}
else
{
  echo "no such prod :(";
}

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
