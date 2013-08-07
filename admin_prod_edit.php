<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");
include_once("include_pouet/box-prod-submit.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("prod.php?which=".(int)$_GET["which"]);
  exit();
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProd extends PouetBoxSubmitProd 
{
  function PouetBoxAdminEditProd( $id ) 
  {
    parent::__construct();

    $this->id = (int)$id;
    
    $this->prod = PouetProd::Spawn( $this->id );
    $a = array(&$this->prod);
    PouetCollectPlatforms( $a );

    $this->title = "edit this prod: ".$this->prod->RenderLink();
  }
  function Commit($data) 
  {
    //die("almost there");
    $a = array();
    $a["name"] = $data["name"];
    $a["download"] = $data["download"];
    
    if ($data["releaseDate_month"] && $data["releaseDate_year"])
      $a["date"] = sprintf("%04d-%02d-15",$data["releaseDate_year"],$data["releaseDate_month"]);
    else if ($data["releaseDate_year"])
      $a["date"] = sprintf("%04d-00-15",$data["releaseDate_year"]);
    else
      $a["date"] = null;
      
    $a["type"] = implode(",",$data["type"]);
        
    $groups = array();
    if ($data["group1"]) $groups[] = (int)$data["group1"];
    if ($data["group2"]) $groups[] = (int)$data["group2"];
    if ($data["group3"]) $groups[] = (int)$data["group3"];
    $groups = array_unique($groups);
    if (count($groups)) $a["group1"] = array_shift($groups); else $a["group1"] = 0;
    if (count($groups)) $a["group2"] = array_shift($groups); else $a["group2"] = 0;
    if (count($groups)) $a["group3"] = array_shift($groups); else $a["group3"] = 0;
    
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
        move_uploaded_file( $_FILES["screenshot"]["tmp_name"], get_local_screenshot_path( (int)$this->id, $extension ) );
    }    
    if(is_uploaded_file($_FILES["nfofile"]["tmp_name"])) 
    {
      move_uploaded_file( $_FILES["nfofile"]["tmp_name"], get_local_nfo_path( (int)$this->id ) );
    }

    gloperator_log( "prod", (int)$this->id, "prod_edit" );

    return array();
  }
  function LoadFromDB()
  {
    parent::LoadFromDB();
    
    $prod = $this->prod;
    
    $a = array();
    $this->fields["name"]["value"] = $prod->name;
    $this->fields["download"]["value"] = $prod->download;
    
    $this->fields["group1"]["value"] = $prod->group1->id;
    $this->fields["group2"]["value"] = $prod->group2->id;
    $this->fields["group3"]["value"] = $prod->group3->id;

    $this->fields["releaseDate"]["value"] = $prod->date;

    $this->fields["platform"]["value"] = $prod->platforms;
    $this->fields["type"]["value"] = $prod->types;
    
    if (count($prod->placings) > 0)
    {
      $this->fields["partyID"]["value"] = $prod->placings[0]->party->id;
      $this->fields["partyYear"]["value"] = $prod->placings[0]->year;
      $this->fields["partyCompo"]["value"] = $prod->placings[0]->compo;
      $this->fields["partyRank"]["value"] = $prod->placings[0]->ranking;
    }
    
    $this->fields["sceneOrgID"]["value"] = $prod->sceneorg;
    $this->fields["zxdemoID"]["value"] = $prod->zxdemo;
    $this->fields["csdbID"]["value"] = $prod->csdb;
    $this->fields["invitationParty"]["value"] = $prod->invitation;
    $this->fields["invitationYear"]["value"] = $prod->invitationyear;
   
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminDeleteProd extends PouetBox 
{
  function PouetBoxAdminDeleteProd( $prod ) 
  {
    parent::__construct();
    
    $this->uniqueID = "pouetbox_proddelete";
    
    $this->classes[] = "errorbox";
    
    $this->prod = $prod;
    
    $strings = array(
      "CELEBRANDIL-VECTOR",
      "MEKKA-SYMPOSIUM",
    );
    $this->checkString = $strings[ array_rand($strings) ];

    $this->title = "delete this prod: ".$this->prod->RenderLink();
  }
  function Validate($data)
  {
    if ($data["check"] != $data["checkOrig"])
      return array("wrong verification string !");
    return array();
  }
  function Commit($data) 
  {
    SQLLib::Query(sprintf_esc("DELETE FROM prods WHERE id=%d LIMIT 1",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM downloadlinks WHERE prod=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM comments WHERE which=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM nfos WHERE prod=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM screenshots WHERE prod=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods_platforms WHERE prod=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM sceneorgrecommended WHERE prodid=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM users_cdcs WHERE cdc=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM affiliatedprods WHERE original=%d or derivative=%d",$this->prod->id,$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods_refs WHERE prod=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prodotherparty WHERE prod=%d",$this->prod->id));
    SQLLib::Query(sprintf_esc("DELETE FROM cdc WHERE which=%d",$this->prod->id));

    unlink( get_local_nfo_path( (int)$this->prod->id ) );
    foreach( array( "jpg","gif","png" ) as $v )
      unlink( get_local_screenshot_path( (int)$this->prod->id, $v ) );

    gloperator_log( "prod", (int)$this->prod->id, "prod_delete", get_object_vars($this->prod) );

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
<script type="text/javascript">
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
    <?
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdSceneorg extends PouetBox 
{
  function PouetBoxAdminEditProdSceneorg( $prod ) 
  {
    parent::__construct();
    
    $this->uniqueID = "pouetbox_prodeditprodsceneorg";
    $this->prod = $prod;
    $this->title = "scene.org recommendations";
    
    $this->sceneorg = SQLLib::SelectRows(sprintf_esc("select * from sceneorgrecommended where prodid = %d",$this->prod->id));

    $row = SQLLib::selectRow("DESC sceneorgrecommended type");
    preg_match_all("/'([^']+)'/",$row->Type,$m);
    $this->types = $m[1];

    $row = SQLLib::selectRow("DESC sceneorgrecommended category");
    preg_match_all("/'([^']+)'/",$row->Type,$m);
    $this->categories = $m[1];
  }
  function Commit($data) 
  {
    if ($data["delSceneorgRec"])
    {
      SQLLib::Query("delete from sceneorgrecommended where id=".(int)$data["delSceneorgRec"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_sceneorg_delete" );
      return array();
    }
    
    $a = array();
    $a["type"] = $data["type"];
    $a["category"] = $data["category"];
    if ($data["editSceneorgRecID"])
    {
      SQLLib::UpdateRow("sceneorgrecommended",$a,"id=".(int)$data["editSceneorgRecID"]);
      $a["id"] = $data["editSceneorgRecID"];

      gloperator_log( "prod", (int)$this->prod->id, "prod_sceneorg_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["prodid"] = $this->prod->id;
      $a["id"] = SQLLib::InsertRow("sceneorgrecommended",$a);

      gloperator_log( "prod", (int)$this->prod->id, "prod_sceneorg_add", array("id"=>$a["id"]) );
    }
    if ($data["partial"])
    {
      $this->RenderNormalRow(toObject($a));
      exit();
    }
    return array();
  }
  function GetRow($id)
  { 
    foreach($this->sceneorg as $v)
      if ($v->id == $id)
        return $v;
    return new stdClass();
  }
  function RenderEditRow($row)
  {
    echo "    <td><select name='type' class='sceneOrgType'>\n";
    foreach($this->types as $v)
      printf("<option%s>%s</option>",$row->type==$v?" selected='selected'":"",_html($v));
    echo "</select></td>\n";
    echo "    <td><select name='category' class='sceneOrgCategory'>\n";
    foreach($this->categories as $v)
      printf("<option%s>%s</option>",$row->category==$v?" selected='selected'":"",_html($v));
    echo "</select></td>\n";
    echo "<td>";
    if ($row->id)
      echo "<input type='hidden' name='editSceneorgRecID' value='".$row->id."'/>";
    echo "<input type='submit' value='Submit'/>";
    echo "</td>\n";
  }
  function RenderNormalRow($v)
  {
    echo "    <td><img src='".POUET_CONTENT_URL."gfx/sceneorg/"._html($v->type).".gif'/> "._html($v->type)."</td>\n";
    echo "    <td>"._html($v->category)."</td>\n";
    printf("    <td><a href='%s?which=%d&amp;editSceneorgRec=%d' class='edit'>edit</a> | <a href='%s?which=%d&amp;delSceneorgRec=%d' class='delete'>delete</a></td>\n",
      $_SERVER["SCRIPT_NAME"],$this->prod->id,$v->id,
      $_SERVER["SCRIPT_NAME"],$this->prod->id,$v->id);
  }
  function RenderBody() 
  {
    echo "<table class='boxtable'>\n";
    echo "  <tr>\n";
    echo "    <th>type</th>\n";
    echo "    <th>category</th>\n";
    echo "    <th>&nbsp;</th>\n";
    echo "  </tr>\n";
    foreach($this->sceneorg as $v)
    {
      echo "  <tr>\n";
      if ($_GET["editSceneorgRec"] == $v->id)
      {
        $this->RenderEditRow($v);
      }
      else
      {
        $this->RenderNormalRow($v);
      }
      echo "  </tr>\n";
    }
    if ($_GET["newSceneorgRec"])
    {
      $this->RenderEditRow( new stdClass() );
    }
    echo "</table>\n";
    echo "<div class='foot'>";
    printf("<a href='%s?which=%d&amp;newSceneorgRec=true' class='new'>new</a>",$_SERVER["SCRIPT_NAME"],$this->prod->id);
    echo "</div>\n";
?>
<script language="JavaScript" type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodsceneorg"), "prodSceneorg",{
    onRowLoad: function(tr){
      tr.down(".sceneOrgType").observe("change",function(e){
        if(e.element().options[ e.element().selectedIndex ].value == "viewingtip")
        {
          tr.down(".sceneOrgCategory").selectedIndex = $A(tr.down(".sceneOrgCategory").options).indexOf( $A(tr.down(".sceneOrgCategory").options).detect(function(item){ return item.value == "viewing tip"; }) );
        }
      });
    }
  } );
});
//-->
</script>
<?    
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdLinks extends PouetBox 
{
  function PouetBoxAdminEditProdLinks( $prod ) 
  {
    parent::__construct();
    
    $this->uniqueID = "pouetbox_prodeditprodlinks";
    $this->prod = $prod;
    $this->title = "additional links";
    
    $this->links = SQLLib::SelectRows(sprintf_esc("select * from downloadlinks where prod = %d",$this->prod->id));
  }
  function Commit($data) 
  {
    if ($data["delLink"])
    {
      SQLLib::Query("delete from downloadlinks where id=".(int)$data["delLink"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_link_del" );
      return array();
    }
    
    $a = array();
    $a["type"] = $data["type"];
    $a["link"] = $data["link"];
    if ($data["editLinkID"])
    {
      SQLLib::UpdateRow("downloadlinks",$a,"id=".(int)$data["editLinkID"]);
      $a["id"] = $data["editLinkID"];
      gloperator_log( "prod", (int)$this->prod->id, "prod_link_add", array("id"=>$a["id"]) );
    }
    else
    {
      $a["prod"] = $this->prod->id;
      $a["id"] = SQLLib::InsertRow("downloadlinks",$a);
      gloperator_log( "prod", (int)$this->prod->id, "prod_link_edit", array("id"=>$a["id"]) );
    }
    if ($data["partial"])
    {
      $this->RenderNormalRow(toObject($a));
      exit();
    }
    return array();
  }
  function GetRow($id)
  { 
    foreach($this->links as $v)
      if ($v->id == $id)
        return $v;
    return new stdClass();
  }
  function RenderEditRow($row)
  {
    echo "    <td><input name='type' value='"._html($row->type)."'/></td>\n";
    echo "    <td><input name='link' value='"._html($row->link)."' type='url'/></td>\n";
    echo "<td>";
    if ($row->id)
      echo "<input type='hidden' name='editLinkID' value='".$row->id."'/>";
    echo "<input type='submit' value='Submit'/>";
    echo "</td>\n";
  }
  function RenderNormalRow($v)
  {
    echo "    <td>"._html($v->type)."</td>\n";
    echo "    <td>"._html($v->link)."</td>\n";
    printf("    <td><a href='%s?which=%d&amp;editLink=%d' class='edit'>edit</a> | <a href='%s?which=%d&amp;delLink=%d' class='delete'>delete</a></td>\n",
      $_SERVER["SCRIPT_NAME"],$this->prod->id,$v->id,
      $_SERVER["SCRIPT_NAME"],$this->prod->id,$v->id);
  }
  function RenderBody() 
  {
    echo "<table class='boxtable'>\n";
    echo "  <tr>\n";
    echo "    <th>type</th>\n";
    echo "    <th>link</th>\n";
    echo "    <th>&nbsp;</th>\n";
    echo "  </tr>\n";
    foreach($this->links as $v)
    {
      echo "  <tr>\n";
      if ($_GET["editLink"] == $v->id)
      {
        $this->RenderEditRow($v);
      }
      else
      {
        $this->RenderNormalRow($v);
      }
      echo "  </tr>\n";
    }
    if ($_GET["newLink"])
    {
      $this->RenderEditRow( new stdClass() );
    }
    echo "</table>\n";
    echo "<div class='foot'>";
    printf("<a href='%s?which=%d&amp;newLink=true' class='new'>new</a>",$_SERVER["SCRIPT_NAME"],$this->prod->id);
    echo "</div>\n";
?>
<script language="JavaScript" type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodlinks"), "prodLinks" );
});
//-->
</script>
<?    
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdParties extends PouetBox 
{
  function PouetBoxAdminEditProdParties( $prod ) 
  {
    parent::__construct();
    
    $this->uniqueID = "pouetbox_prodeditprodparties";
    $this->prod = $prod;
    $this->title = "additional parties";
    
    $s = new BM_Query();
    $s->AddField("prodotherparty.id");
    $s->AddField("prodotherparty.partycompo");
    $s->AddField("prodotherparty.party_place");
    $s->AddField("prodotherparty.party_year");
    $s->AddTable("prodotherparty");
    $s->attach(array("prodotherparty"=>"party"),array("parties as party"=>"id"));
    $s->AddWhere(sprintf_esc("prod=%d",$this->prod->id));
    $this->parties = $s->perform();
    
    
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
  function Commit($data) 
  {
    if ($data["delParty"])
    {
      SQLLib::Query("delete from prodotherparty where id=".(int)$data["delParty"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_party_del" );
      return array();
    }
    
    $a = array();
    $a["party"] = $data["partyID"];
    $a["party_year"] = $data["partyYear"];
    $a["party_place"] = $data["partyPlace"];
    $a["partycompo"] = $data["partyCompo"];
    if ($data["editPartyID"])
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
    if ($data["partial"])
    {
      $o = toObject($a);
      $o->party = PouetParty::Spawn($a["party"]);
      $this->RenderNormalRow($o);
      exit();
    }
    return array();
  }
  function GetRow($id)
  { 
    foreach($this->parties as $v)
      if ($v->id == $id)
        return $v;
    return new stdClass();
  }
  function RenderEditRow($row)
  {
    echo "    <td><input name='partyID' value='"._html($row->party?$row->party->id:"")."' class='partyID'/></td>\n";

    echo "    <td><select name='partyYear'>";
    foreach($this->years as $k=>$v)
      printf("<option value='%s'%s>%s</option>",_html($k),($k == $row->party_year) ? " selected='selected'" : "",_html($v));
    echo "</select></td>\n";

    echo "    <td><select name='partyCompo'>";
    foreach($this->compos as $v)
      printf("<option value='%s'%s>%s</option>",_html($v),($v == $row->partycompo) ? " selected='selected'" : "",_html($v));
    echo "</select></td>\n";

    echo "    <td><select name='partyPlace'>";
    foreach($this->ranks as $k=>$v)
      printf("<option value='%s'%s>%s</option>",_html($k),($k == $row->party_place) ? " selected='selected'" : "",_html($v));
    echo "</select></td>\n";

//    echo "    <td><input name='partyCompo' value='"._html($row->partycompo)."'/></td>\n";
//    echo "    <td><input name='partyPlace' value='"._html($row->party_place)."'/></td>\n";
    echo "<td>";
    if ($row->id)
      echo "<input type='hidden' name='editPartyID' value='".$row->id."'/>";
    echo "<input type='submit' value='Submit'/>";
    echo "</td>\n";
  }
  function RenderNormalRow($v)
  {
    echo "    <td>"._html($v->party->name)."</td>\n";
    echo "    <td>"._html($v->party_year)."</td>\n";
    echo "    <td>"._html($v->partycompo)."</td>\n";
    echo "    <td>"._html($v->party_place)."</td>\n";
    printf("    <td><a href='%s?which=%d&amp;editParty=%d' class='edit'>edit</a> | <a href='%s?which=%d&amp;delParty=%d' class='delete'>delete</a></td>\n",
      $_SERVER["SCRIPT_NAME"],$this->prod->id,$v->id,
      $_SERVER["SCRIPT_NAME"],$this->prod->id,$v->id);
  }
  function RenderBody() 
  {
    echo "<table class='boxtable'>\n";
    echo "  <tr>\n";
    echo "    <th>party</th>\n";
    echo "    <th>year</th>\n";
    echo "    <th>compo</th>\n";
    echo "    <th>place</th>\n";
    echo "    <th>&nbsp;</th>\n";
    echo "  </tr>\n";
    foreach($this->parties as $v)
    {
      echo "  <tr>\n";
      if ($_GET["editParty"] == $v->id)
      {
        $this->RenderEditRow($v);
      }
      else
      {
        $this->RenderNormalRow($v);
      }
      echo "  </tr>\n";
    }
    if ($_GET["newParty"])
    {
      $this->RenderEditRow( new stdClass() );
    }
    echo "</table>\n";
    echo "<div class='foot'>";
    printf("<a href='%s?which=%d&amp;newParty=true' class='new'>new</a>",$_SERVER["SCRIPT_NAME"],$this->prod->id);
    echo "</div>\n";
?>
<script language="JavaScript" type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodparties"), "prodParties", {
    onRowLoad: function(tr){
      new Autocompleter(tr.down(".partyID"), {"dataUrl":"./ajax_parties.php"});
    }
  } );
});
//-->
</script>
<?    
  }
}


///////////////////////////////////////////////////////////////////////////////

class PouetBoxAdminEditProdAffil extends PouetBox 
{
  function PouetBoxAdminEditProdAffil( $prod ) 
  {
    parent::__construct();
    
    $this->uniqueID = "pouetbox_prodeditprodaffil";
    $this->prod = $prod;
    $this->title = "related prods";
    
    $s = new BM_Query();
    $s->AddField("affiliatedprods.id");
    $s->AddField("affiliatedprods.type");
    $s->AddTable("affiliatedprods");
    $s->attach(array("affiliatedprods"=>"original"),array("prods as prodOriginal"=>"id"));
    $s->attach(array("affiliatedprods"=>"derivative"),array("prods as prodDerivative"=>"id"));
    $s->AddWhere(sprintf_esc("original=%d or derivative=%d",$this->prod->id,$this->prod->id));
    $this->prods = $s->perform();
  }
  function Commit($data) 
  {
    if ($data["delRelation"])
    {
      SQLLib::Query("delete from affiliatedprods where id=".(int)$data["delRelation"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_rel_del" );
      return array();
    }
    
    list($direction,$type) = explode(":",$data["type"],2);
    $a = array();
    $a["type"] = $type;
    $a["original"]   = $direction == "o" ? $this->prod->id : $data["prod"];
    $a["derivative"] = $direction == "d" ? $this->prod->id : $data["prod"];
    if ($data["editRelationID"])
    {
      SQLLib::UpdateRow("affiliatedprods",$a,"id=".(int)$data["editRelationID"]);
      $a["id"] = $data["editRelationID"];
      gloperator_log( "prod", (int)$this->prod->id, "prod_rel_edit", array("id"=>$a["id"]) );
    }
    else
    {
      $a["id"] = SQLLib::InsertRow("affiliatedprods",$a);
      gloperator_log( "prod", (int)$this->prod->id, "prod_rel_add", array("id"=>$a["id"]) );
    }
    if ($data["partial"])
    {
      $o = toObject($a);
      $o->prodOriginal   = PouetProd::Spawn($a["original"]);
      $o->prodDerivative = PouetProd::Spawn($a["derivative"]);
      $this->RenderNormalRow($o);
      exit();
    }
  
    return array();
  }
  function GetRow($id)
  { 
    foreach($this->prods as $v)
      if ($v->id == $id)
        return $v;
    return new stdClass();
  }
  function RenderEditRow($row)
  {
    global $AFFILIATIONS_ORIGINAL;
    global $AFFILIATIONS_INVERSE;

    $a = ($this->prod->id == $v->prodOriginal->id ? $AFFILIATIONS_ORIGINAL : $AFFILIATIONS_INVERSE);

    //echo "    <td><input name='type' value='"._html(($this->prod->id == $row->prodOriginal->id ? "o" : "d").":".$row->type)."'/></td>\n";
    echo "<td><select name='type'>";
    foreach($AFFILIATIONS_ORIGINAL as $k=>$v)
      printf("<option value='o:%s'%s>%s</option>",$k,($this->prod->id == $row->prodOriginal->id && $k == $row->type) ? " selected='selected'" : "",$v);
    foreach($AFFILIATIONS_INVERSE as $k=>$v)
      printf("<option value='d:%s'%s>%s</option>",$k,($this->prod->id == $row->prodDerivative->id && $k == $row->type) ? " selected='selected'" : "",$v);
    echo "</select></td>\n";

    echo "    <td><input name='prod' value='"._html( $this->prod->id == $row->prodOriginal->id ? $row->prodDerivative->id : $row->prodOriginal->id)."' class='prodID'/></td>\n";
    echo "<td>";
    if ($row->id)
      echo "<input type='hidden' name='editRelationID' value='".$row->id."'/>";
    echo "<input type='submit' value='Submit'/>";
    echo "</td>\n";
  }
  function RenderNormalRow($v)
  {
    global $AFFILIATIONS_ORIGINAL;
    global $AFFILIATIONS_INVERSE;
    
    $a = ($this->prod->id == $v->prodOriginal->id ? $AFFILIATIONS_ORIGINAL : $AFFILIATIONS_INVERSE);
    $prod = ($this->prod->id == $v->prodOriginal->id ? $v->prodDerivative : $v->prodOriginal);
    echo "    <td>"._html($a[$v->type])."</td>\n";
    echo "    <td>".($prod ? $prod->RenderLink() : "")."</td>\n";
    printf("    <td><a href='%s?which=%d&amp;editRelation=%d' class='edit'>edit</a> | <a href='%s?which=%d&amp;delRelation=%d' class='delete'>delete</a></td>\n",
      $_SERVER["SCRIPT_NAME"],$this->prod->id,$v->id,
      $_SERVER["SCRIPT_NAME"],$this->prod->id,$v->id);
  }
  function RenderBody() 
  {
    echo "<table class='boxtable'>\n";
    echo "  <tr>\n";
    echo "    <th>relation</th>\n";
    echo "    <th>prod</th>\n";
    echo "    <th>&nbsp;</th>\n";
    echo "  </tr>\n";
    foreach($this->prods as $v)
    {
      echo "  <tr>\n";
      if ($_GET["editRelation"] == $v->id)
      {
        $this->RenderEditRow($v);
      }
      else
      {
        $this->RenderNormalRow($v);
      }
      echo "  </tr>\n";
    }
    if ($_GET["newParty"])
    {
      $this->RenderEditRow( new stdClass() );
    }
    echo "</table>\n";
    echo "<div class='foot'>";
    printf("<a href='%s?which=%d&amp;newRelation=true' class='new'>new</a>",$_SERVER["SCRIPT_NAME"],$this->prod->id);
    echo "</div>\n";
?>
<script language="JavaScript" type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_prodeditprodaffil"), "prodAffil", {
    onRowLoad: function(tr){
      new Autocompleter(tr.down(".prodID"), {"dataUrl":"./ajax_prods.php"});
    }
  } );
});
//-->
</script>
<?    
  }
}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

if($_GET["partial"] && $currentUser && $currentUser->CanEditItems())
{
  // ajax responses
  $prod = new stdClass();
  $prod->id = $_GET["which"];
  if ($_GET["editSceneorgRec"])
  {
    $box = new PouetBoxAdminEditProdSceneorg( $prod );
    $box->RenderEditRow( $box->GetRow( $_GET["editSceneorgRec"] ) );
  }
  if ($_GET["newSceneorgRec"])
  {
    $box = new PouetBoxAdminEditProdSceneorg( $prod );
    $box->RenderEditRow( new stdClass() );
  }
  if ($_GET["editLink"])
  {
    $box = new PouetBoxAdminEditProdLinks( $prod );
    $box->RenderEditRow( $box->GetRow( $_GET["editLink"] ) );
  }
  if ($_GET["newLink"])
  {
    $box = new PouetBoxAdminEditProdLinks( $prod );
    $box->RenderEditRow( new stdClass() );
  }
  if ($_GET["editParty"])
  {
    $box = new PouetBoxAdminEditProdParties( $prod );
    $box->RenderEditRow( $box->GetRow( $_GET["editParty"] ) );
  }
  if ($_GET["newParty"])
  {
    $box = new PouetBoxAdminEditProdParties( $prod );
    $box->RenderEditRow( new stdClass() );
  }
  if ($_GET["editRelation"])
  {
    $box = new PouetBoxAdminEditProdAffil( $prod );
    $box->RenderEditRow( $box->GetRow( $_GET["editRelation"] ) );
  }
  if ($_GET["newRelation"])
  {
    $box = new PouetBoxAdminEditProdAffil( $prod );
    $box->RenderEditRow( new stdClass() );
  }
  exit(); 
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "prod.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditProd( $_GET["which"] );
$form->Add( "prod", $box );
$form->Add( "prodLinks", new PouetBoxAdminEditProdLinks($box->prod) );
$form->Add( "prodParties", new PouetBoxAdminEditProdParties($box->prod) );
$form->Add( "prodSceneorg", new PouetBoxAdminEditProdSceneorg($box->prod) );
$form->Add( "prodAffil", new PouetBoxAdminEditProdAffil($box->prod) );
if ($currentUser && $currentUser->CanDeleteItems())
  $form->Add( "prodDelete", new PouetBoxAdminDeleteProd($box->prod) );

if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit a prod: ".$box->prod->name;

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if ($box->prod)
{
  if (get_login_id())
  {
    $form->Display();
  ?>
  <script type="text/javascript">
  document.observe("dom:loaded",function(){
    if (!$("row_csdbID")) return;
    PrepareSubmitForm();
  });
  </script>
  <?
  }
  else
  {
    include_once("include_pouet/box-login.php");
    $box = new PouetBoxLogin();
    $box->Render();
  }
}
else
{
  echo "no such prod :(";
}

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");

?>
