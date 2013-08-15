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
  static function getFields() { return array("id","name","type","views","added","quand","date",
    "voteup","votepig","votedown","voteavg","download","partycompo","party_place","party_year"); }
  static function getExtendedFields() { return array("sceneorg","csdb","zxdemo","latestip","invitation","invitationyear","boardID","rank"); }

  function onFinishedPopulate() {
    $this->groups = array();
    if ($this->group1) $this->groups[] = $this->group1;
    if ($this->group2) $this->groups[] = $this->group2;
    if ($this->group3) $this->groups[] = $this->group3;

    $this->types = explode(",",$this->type);
    if ($this->party && $this->party->id != NO_PARTY_ID)
      $this->placings[] = new PouetPlacing( array("party"=>$this->party,"compo"=>$this->partycompo,"ranking"=>$this->party_place,"year"=>$this->party_year) );
  }
  static function onAttach( &$node, &$query )
  {
    $node->attach( $query, "group1", array("groups as group1"=>"id"));
    $node->attach( $query, "group2", array("groups as group2"=>"id"));
    $node->attach( $query, "group3", array("groups as group3"=>"id"));
    $node->attach( $query, "party", array("parties as party"=>"id"));
    $node->attach( $query, "added", array("users as addeduser"=>"id"));
  }

  function RenderTypeIcons() {
    $s = "<span class='typeiconlist'>";
    foreach($this->types as $t)
      $s .= "<span class='typei type_".str_replace(" ","_",$t)."' title='"._html($t)."'>".$t."</span>\n";
    $s .= "</span>";
    return $s;
  }
  function RenderPlatformIcons() {
    global $PLATFORMS;
    $s = "<span class='platformiconlist'>";
    foreach($this->platforms as $t)
      $s .= "<span class='platformi os_".$PLATFORMS[$t]["slug"]."' title='"._html($PLATFORMS[$t]["name"])."'>".$PLATFORMS[$t]["name"]."</span>\n";
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
    global $PLATFORMS;
    $s = "<ul>";
    foreach($this->platforms as $t)
      $s .= "<li><a href='prodlist.php?platform[]=".rawurlencode($PLATFORMS[$t]["name"])."'><span class='platform os_".$PLATFORMS[$t]["slug"]."'>".$PLATFORMS[$t]["name"]."</span> ".$PLATFORMS[$t]["name"]."</a></li>\n";
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
    		printf("<a href='sceneorg.php#%s'><img src=\"".POUET_CONTENT_URL."gfx/sceneorg/%s.gif\" title=\"%s\" alt=\"%s\"></a>",
    		  $a->type == "viewingtip" ? substr($this->date,0,4) : substr($this->date,0,4) . str_replace(" ","",$a->category),
    		  $a->type,
    		  $a->category,
    		  $a->category);
  		}
      echo "</div>";
		}

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
    if (!$this->date || $this->date{0}=="0") return "";
    if (substr($this->date,5,2)=="00")
      return substr($this->date,0,4);
    return strtolower(date("F Y",strtotime($this->date)));
  }
  function RenderAddedDate() {
    if (!$this->quand) return "";
    if (substr($this->quand,5,2)=="00")
      return substr($this->quand,0,4);
    return strtolower(date("F Y",strtotime($this->quand)));
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
};

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
        $v->platforms[] = $r->platform;
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
