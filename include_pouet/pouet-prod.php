<?php
class PouetProd extends BM_Class 
{
  public $id;
  public $name;
  public $type;
  public $views;
  public $addedUser;
  public $addedDate;
  public $releaseDate;
  public $voteup;
  public $votepig;
  public $votedown;
  public $voteavg;
  public $download;
  public $party_compo;
  public $party_place;
  public $party_year;
  
  public $sceneorg;
  public $demozoo;
  public $csdb;
  public $zxdemo;
  public $latestip;
  public $invitation;
  public $invitationyear;
  public $boardID;
  public $rank;

  public $group1;
  public $group2;
  public $group3;

  // non-db types
  public $groups = array();
  public $types = array();
  public $placings = array();
  public $platforms = array();
  public $awards = array();
  public $party;
  
  public $cdc;

  function __construct()
  {
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

    if ($this->type)
    {
      $this->types = explode(",",$this->type);
    }
    if ($this->party && $this->party->id != NO_PARTY_ID)
      $this->placings[] = new PouetPlacing( array("party"=>$this->party,"compo"=>$this->party_compo,"ranking"=>$this->party_place,"year"=>$this->party_year) );
  }
  static function onAttach( &$node, &$query )
  {
    $node->attach( $query, "group1", array("groups as group1"=>"id"));
    $node->attach( $query, "group2", array("groups as group2"=>"id"));
    $node->attach( $query, "group3", array("groups as group3"=>"id"));
    $node->attach( $query, "party", array("parties as party"=>"id"));
    $node->attach( $query, "addedUser", array("users as addedUser"=>"id"));
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
      $s .= "<li><a href='prodlist.php?type%5B%5D=".rawurlencode($t)."'><span class='type type_".str_replace(" ","_",$t)."'>".$t."</span> ".$t."</a></li>\n";
    $s .= "</ul>";
    return $s;
  }
  function RenderPlatformNames() {
    $s = "<ul>";
    foreach($this->platforms as $t)
      $s .= "<li><a href='prodlist.php?platform%5B%5D=".rawurlencode($t["name"])."'><span class='platform os_".$t["slug"]."'>".$t["name"]."</span> ".$t["name"]."</a></li>\n";
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
  function RenderAwards()
  {
    global $AWARDS_CATEGORIES;

    if (!$this->awards)
    {
      return;
    }

    echo "<ul class='awards'>";
    foreach($this->awards as $a)
    {
      $category = $AWARDS_CATEGORIES[$a->categoryID];
      $year = $this->releaseDate ? substr($this->releaseDate,0,4) : "";
      $title = $category->series." - ".$category->category;
      if ($a->awardType == "nominee")
      {
        $title .= " (Nominee)";
      }

      printf("<li><a class='%s %s' href='awards.php#%s' title='%s'>%s</a></a>",
        $category->cssClass,
        $a->awardType,
        hashify($category->series." ".$year." ".$category->category),
        _html($title),
        _html($title));
    }
    echo "</ul>";
  }
  function RenderAccolades()
  {
    if ($this->cdc)
    {
      cdcstack( $this->cdc );
    }

    $this->RenderAwards();
  }
  function RenderAvgRaw() {
    return sprintf("%.2f",$this->voteavg);
  }
  function RenderAvg() {
    $rating = "isok";
    if ($this->voteavg < 0) $rating = "sucks";
    if ($this->voteavg > 0) $rating = "rulez";
    return "<span class='".$rating."' title='".$rating."'>".$this->RenderAvgRaw()."</span>";
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
    global $currentUser;
    if (!($currentUser && $currentUser->CanDeleteItems()))
      return;

    SQLLib::Query(sprintf_esc("DELETE FROM downloadlinks WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM comments WHERE which=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM nfos WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM screenshots WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods_platforms WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM awards WHERE prodID=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM users_cdcs WHERE cdc=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM affiliatedprods WHERE original=%d or derivative=%d",$this->id,$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods_refs WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prodotherparty WHERE prod=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM cdc WHERE which=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM credits WHERE prodID=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM watchlist WHERE prodID=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM list_items WHERE itemid=%d AND type='prod'",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods_linkcheck WHERE prodID=%d LIMIT 1",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM modification_requests WHERE itemID=%d AND itemType='prod'",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM prods WHERE id=%d LIMIT 1",$this->id));

    @unlink( get_local_nfo_path( (int)$this->id ) );
    foreach( array( "jpg","gif","png" ) as $v )
      @unlink( get_local_screenshot_path( (int)$this->id, $v ) );

    gloperator_log( "prod", (int)$this->id, "prod_delete", get_object_vars($this) );
  }
  static function RecalculateVoteCacheByID($prodID)
  {
    $rulez=0;
    $piggie=0;
    $sucks=0;
    $total=0;
    $checktable = array();

    $r = SQLLib::SelectRows("SELECT rating,who FROM comments WHERE which=".$prodID);
    foreach ($r as $t)
      if(!array_key_exists($t->who, $checktable) || $t->rating != 0)
        $checktable[$t->who] = $t->rating;

    foreach($checktable as $k=>$v)
    {
      if($v==1) $rulez++;
      else if($v==-1) $sucks++;
      else $piggie++;
      $total++;
    }

    $avg = 0;
    if ($total != 0)
    {
      $avg = (float)($rulez - $sucks) / (float)$total;
    }

    $a = array();
    $a["voteup"] = $rulez;
    $a["votepig"] = $piggie;
    $a["votedown"] = $sucks;
    $a["voteavg"] = $avg;
    SQLLib::UpdateRow("prods",$a,"id=".$prodID);
  }
  function RecalculateVoteCache()
  {
    PouetProd::RecalculateVoteCacheByID($this->id);
  }

  use PouetAPI { ToAPI as protected ToAPISuper; }

  function ToAPI()
  {
    $array = $this->ToAPISuper();

    $array["popularity"] = calculate_popularity($this->views);
    
    $screenshot = find_screenshot( $this->id );
    if ($screenshot)
    {
      $array["screenshot"] = POUET_CONTENT_URL . $screenshot;
    }

    if ($this->party_compo)
    {
      global $COMPOTYPES;
      $array["party_compo_name"] = $COMPOTYPES[ $this->party_compo ];
    }
    //foreach($array["placings"] as &$p)
    //  $p->compo_name = $COMPOTYPES[ $p->compo ];

    unset($array["group1"]);
    unset($array["group2"]);
    unset($array["group3"]);
    unset($array["views"]);
    unset($array["latestip"]);
    return $array;
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

  $rows = SQLLib::selectRows("select * from awards where prodID in (".implode(",",$ids).") order by awardType, categoryID");
  foreach($prodArray as &$v)
  {
    foreach($rows as &$r)
    {
      if ($v->id == $r->prodID)
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