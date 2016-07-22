<?
class PouetProd extends BM_Class {
  var $types;
  var $platforms;
  var $placings;
  var $groups;
  var $group1;
  var $group2;
  var $group3;

  function PouetProd()
  {
    $this->types = array();
    $this->platforms = array();
    $this->placings = array();
    $this->awards = array();
  }
  static function getTable () { return "prods"; }
  static function getFields() { return array("id","name","type","views","addedUser","addedDate","releaseDate",
    "voteup","votepig","votedown","voteavg","download","party_compo","party_place","party_year"); }
  static function getExtendedFields() { return array("sceneorg","demozoo","csdb","zxdemo","latestip","invitation","invitationyear","boardID","rank"); }

  function onFinishedPopulate() {
    $this->groups = array();
    if ($this->group1) $this->groups[] = $this->group1; unset($this->group1);
    if ($this->group2) $this->groups[] = $this->group2; unset($this->group2);
    if ($this->group3) $this->groups[] = $this->group3; unset($this->group3);

    $this->types = explode(",",$this->type);
    if ($this->party && $this->party->id != NO_PARTY_ID)
      $this->placings[] = new PouetPlacing( array("party"=>$this->party,"compo"=>$this->party_compo,"ranking"=>$this->party_place,"year"=>$this->party_year) );
  }
  static function onAttach( &$node, &$query )
  {
    $node->attach( $query, "group1", array("groups as group1"=>"id"));
    $node->attach( $query, "group2", array("groups as group2"=>"id"));
    $node->attach( $query, "group3", array("groups as group3"=>"id"));
    $node->attach( $query, "party", array("parties as party"=>"id"));
    $node->attach( $query, "addedUser", array("users as addeduser"=>"id"));
  }

  function RenderTypeIcons() {
    $s = "<span class='typeiconlist'>";
    foreach($this->types as $t)
      $s .= "<span class='typei type_".str_replace(" ","_",$t)."' title='"._html($t)."'>".$t."</span>\n";
    $s .= "</span>";
    return $s;
  }
  function RenderPlatformIcons() {
    $s = "<span class='platformiconlist'>";
    foreach($this->platforms as $t)
      $s .= "<span class='platformi os_".$t["slug"]."' title='"._html($t["name"])."'>"._html($t["name"])."</span>\n";
    $s .= "</span>";
    return $s;
  }
  function RenderTypeNames() {
    $s = "<ul>";
    foreach($this->types as $t)
      $s .= "<li><a href='prodlist.php?type[]=".rawurlencode($t)."'><span class='type type_".str_replace(" ","_",$t)."'>".$t."</span> ".$t."</a></li>\n";
    $s .= "</ul>";
    return $s;
  }
  function RenderPlatformNames() {
    $s = "<ul>";
    foreach($this->platforms as $t)
      $s .= "<li><a href='prodlist.php?platform[]=".rawurlencode($t["name"])."'><span class='platform os_".$t["slug"]."'>".$t["name"]."</span> ".$t["name"]."</a></li>\n";
    $s .= "</ul>";
    return $s;
  }
  function RenderGroupsShort() {
    $s = "";
    foreach($this->groups as $g) if ($g)
      $s .= ":: ".$g->RenderShort()."\n";
    return $s;
  }
  function RenderGroupsShortProdlist() {
    $s = array();
    foreach($this->groups as $g) if ($g) {
      $s[] = $g->RenderShort();
    }
    return implode(" :: ",$s);
  }
  function RenderGroupsLong() {
    $s = array();
    foreach($this->groups as $g) if ($g) {
      $s[] = $g->RenderFull();
    }
    return implode(" & ",$s);
  }
  function RenderGroupsPlain() {
    $s = array();
    foreach($this->groups as $g) if ($g) {
      $s[] = $g->name;
    }
    return implode(" & ",$s);
  }
  function RenderAwards() {
    if ($this->cdc)
      cdcstack( $this->cdc );

    if ($this->awards)
    {
      echo "<div class='awards'>";
      foreach($this->awards as $a)
      {
    		printf("<a href='awards.php#%s'><img src=\"".POUET_CONTENT_URL."gfx/sceneorg/%s.gif\" title=\"%s\" alt=\"%s\"></a>",
    		  $a->type == "viewingtip" ? substr($this->releaseDate,0,4) : substr($this->releaseDate,0,4) . hashify($a->category),
    		  _html($a->type),
    		  _html($a->category),
    		  _html($a->category));
  		}
      echo "</div>";
		}

  }
  function GetLink( $root = POUET_ROOT_URL) {
    return sprintf( $root . "prod.php?which=%d",$this->id);
  }
  function RenderLink() {
    return sprintf("<a href='prod.php?which=%d'>%s</a>",$this->id,_html($this->name));
  }
  function RenderLinkTruncated() {
    return sprintf("<a href='prod.php?which=%d'>%s</a>",$this->id,_html(shortify_cut($this->name,40)));
  }
  function RenderSingleRow() {
    $s = "<span class='prod'>".$this->RenderLink()."</span>";
    if ($this->groups)
    {
      $s .= " by ";
      $a = array();
      foreach($this->groups as $g) if ($g) {
        $a[] = $g->RenderFull();
      }
      $s .= implode(" & ",$a);
    }
    return $s;
  }
  function RenderSingleRowShort() {
    $s = "<span class='prod'>".$this->RenderLink()."</span>";
    if ($this->groups)
    {
      $s .= " by ";
      $a = array();
      foreach($this->groups as $g) if ($g) {
        $a[] = $g->RenderLong();
      }
      $s .= implode(" & ",$a);
    }
    return $s;
  }

  function RenderReleaseDate() {
    return renderHalfDate( $this->releaseDate );
  }
  function RenderAddedDate() {
    return renderHalfDate( $this->addedDate );
  }
  function RenderAsEntry() {
    echo "<span class='prodentry'>";
    if (get_setting("indextype"))
      echo $this->RenderTypeIcons();
    if (get_setting("indexplatform"))
      echo $this->RenderPlatformIcons();
    echo "<span class='prod'>".$this->RenderLinkTruncated()."</span>\n";
    echo "<span class='group'>".$this->RenderGroupsShort()."</span>\n";
    echo "</span>";
  }
  function Delete() {
    SQLLib::Query(sprintf_esc("DELETE FROM downloadlinks WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM comments WHERE which=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM nfos WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM screenshots WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods_platforms WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM sceneorgrecommended WHERE prodid=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM users_cdcs WHERE cdc=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM affiliatedprods WHERE original=%d or derivative=%d",$this->id,$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods_refs WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prodotherparty WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM cdc WHERE which=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM credits WHERE prodID=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM watchlist WHERE prodID=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM listitems WHERE itemid=%d AND type='prod'",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods_linkcheck WHERE prodID=%d LIMIT 1",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods WHERE id=%d LIMIT 1",$this->id));

    @unlink( get_local_nfo_path( (int)$this->id ) );
    foreach( array( "jpg","gif","png" ) as $v )
      @unlink( get_local_screenshot_path( (int)$this->id, $v ) );

    gloperator_log( "prod", (int)$this->id, "prod_delete", get_object_vars($this) );
  }
};

///////////////////////////////////////////////////////////////////////////////

function PouetCollectPlatforms( &$prodArray )
{
  $ids = array();
  foreach($prodArray as $v) if ($v->id) $ids[] = $v->id;
  if (!$ids) return;
  $rows = SQLLib::selectRows("select * from prods_platforms where prods_platforms.prod in (".implode(",",$ids).")");
  foreach($prodArray as &$v)
  {
    foreach($rows as &$r)
    {
      if ($v->id == $r->prod)
      {
        global $PLATFORMS;
        $v->platforms[ $r->platform ] = $PLATFORMS[$r->platform];
        unset($r);
      }
    }
  }
}

function PouetCollectAwards( &$prodArray )
{
  $ids = array();
  foreach($prodArray as $v) if ($v->id) $ids[] = $v->id;
  if (!$ids) return;

  $rows = SQLLib::selectRows("select * from sceneorgrecommended where prodid in (".implode(",",$ids).") order by type, category");
  foreach($prodArray as &$v)
  {
    foreach($rows as &$r)
    {
      if ($v->id == $r->prodid)
      {
        $v->awards[] = $r;
        unset($r);
      }
    }
  }

  foreach($prodArray as &$v)
    $v->cdc = 0;

  $rows = SQLLib::selectRows("select which from cdc where which in (".implode(",",$ids).")");
  foreach($prodArray as &$v)
  {
    $v->cdc = 0;
    foreach($rows as &$r)
    {
      if ($v->id == $r->which)
      {
        $v->cdc++;
      }
    }
  }

  $rows = SQLLib::selectRows("select count(*) as c,cdc from users_cdcs where cdc in (".implode(",",$ids).") group by cdc");
  foreach($prodArray as &$v)
  {
    foreach($rows as &$r)
    {
      if ($v->id == $r->cdc)
      {
        $v->cdc += $r->c;
      }
    }
  }
}

BM_AddClass("PouetProd");
?>
