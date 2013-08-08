<?
require_once("bootstrap.inc.php");

class PouetBoxProdlist extends PouetBox
{
  var $id;
  var $group;

  function PouetBoxProdlist() {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodlist";
  }

  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return _html("prodlist.php?" . http_build_query($query));
  }
  function LoadFromDB() {
    $s = new SQLSelect();

    $perPage = get_setting("prodlistprods");
    $this->page = (int)max( 1, (int)$_GET["page"] );

    $s = new BM_Query("prods");
    //$s->AddWhere(sprintf_esc("(prods.group1 = %d) or (prods.group2 = %d) or (prods.group3 = %d)",$this->id,$this->id,$this->id));
    //$s->AddOrder("prods.date DESC, prods.quand DESC");

    if ($_GET["type"])
    {
      $cond = array();
      foreach($_GET["type"] as $type)
        $cond[] = sprintf_esc("FIND_IN_SET('%s',prods.type)",$type);
      $s->AddWhere(implode(" OR ",$cond));
    }
    if ($_GET["platform"])
    {
      global $PLATFORMS;
      $platforms = array();
      foreach($_GET["platform"] as $platform)
        foreach($PLATFORMS as $k=>$v)
          if ($v["name"] == $platform)
            $platforms[] = $k;
      if ($platforms)
      {
        $s->AddJoin("LEFT","prods_platforms as pp","pp.prod = prods.id");
        $s->AddWhere(sprintf_esc("pp.platform in (%s)",implode(",",$platforms)));
      }
    }
    $dir = "DESC";
    if ($_GET["reverse"])
      $dir = "ASC";
    switch($_GET["order"])
    {
      case "type": $s->AddOrder("prods.type ".$dir); break;
      case "name": $s->AddOrder("prods.name ".$dir); break;
      case "group": $s->AddOrder("prods.group1 ".$dir); $s->AddOrder("prods.group2 ".$dir); $s->AddOrder("prods.group3 ".$dir); break;
      case "party": $s->AddOrder("prods_party.name ".$dir); $s->AddOrder("prods.party_year ".$dir); $s->AddOrder("prods.party_place ".$dir); break;
      case "thumbup": $s->AddOrder("prods.voteup ".$dir); break;
      case "thumbpig": $s->AddOrder("prods.votepig ".$dir); break;
      case "thumbdown": $s->AddOrder("prods.votedown ".$dir); break;
      case "avg": $s->AddOrder("prods.voteavg ".$dir); break;
      case "views": $s->AddOrder("prods.views ".$dir); break;
      case "added": $s->AddOrder("prods.quand ".$dir); break;
      //default: $s->AddOrder("prods.date DESC"); $s->AddOrder("prods.quand DESC"); break;
    }
    $s->AddOrder("prods.date ".$dir);
    $s->AddOrder("prods.quand ".$dir);

    $s->SetLimit( $perPage, (int)(($this->page-1) * $perPage) );

    //echo $s->GetQuery();

    $this->prods = $s->performWithCalcRows( $this->count );
    PouetCollectPlatforms($this->prods);
    PouetCollectAwards($this->prods);

    $this->maxviews = SQLLib::SelectRow("SELECT MAX(views) as m FROM prods")->m;
  }

  function Render() {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "type"=>"type",
      "name"=>"prodname",
      "platform"=>"platform",
      "group"=>"group",
      "party"=>"release party",
      "release"=>"release",
      "added"=>"added",
      "thumbup"=>"<img src='http://www.pouet.net/gfx/rulez.gif' alt='rulez' />",
      "thumbpig"=>"<img src='http://www.pouet.net/gfx/isok.gif' alt='piggie' />",
      "thumbdown"=>"<img src='http://www.pouet.net/gfx/sucks.gif' alt='sucks' />",
      "avg"=>"avg",
      "views"=>"popularity",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a href='%s' class='%s%s' id='%s'>%s</a></th>\n",
        $this->BuildURL(array("order"=>$key)),$_GET["order"]==$key?"selected":"",($_GET["order"]==$key && $_GET["reverse"])?" reverse":"","sort_".$key,$text);
      if ($key == "type" || $key == "name") $out = str_replace("</th>","",$out);
      if ($key == "platform" || $key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    foreach ($this->prods as $p) {
      echo "<tr>\n";

      echo "<td>\n";
      echo $p->RenderTypeIcons();
      echo $p->RenderPlatformIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo $p->RenderAwards();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      if ($p->placings)
        echo $p->placings[0]->PrintResult($p->year);
      echo "</td>\n";

      echo "<td class='date'>".$p->RenderReleaseDate()."</td>\n";
      echo "<td class='date'>".$p->RenderAddedDate()."</td>\n";

      echo "<td class='votes'>".$p->voteup."</td>\n";
      echo "<td class='votes'>".$p->votepig."</td>\n";
      echo "<td class='votes'>".$p->votedown."</td>\n";

      $i = "isok";
      if ($p->voteavg < 0) $i = "sucks";
      if ($p->voteavg > 0) $i = "rulez";
      echo "<td class='votes'>".sprintf("%.2f",$p->voteavg)."&nbsp;<img src='http://www.pouet.net/gfx/".$i.".gif' alt='".$i."' /></td>\n";

      $pop = (int)($p->views * 100 / $this->maxviews);
      echo "<td><div class='innerbar_solo' style='width: ".$pop."px'>&nbsp;<span>".$pop."%</span></div></td>\n";

      echo "</tr>\n";
    }

    $perPage = get_setting("prodlistprods");

    echo "<tr>\n";
    echo "<td class='nav' colspan=".(count($headers)-2).">\n";

    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='".$this->BuildURL(array("page"=>($this->page - 1)))."'>previous page</a></div>\n";
    if ($this->page < ($this->count / $perPage))
      echo "  <div class='nextpage'><a href='".$this->BuildURL(array("page"=>($this->page + 1)))."'>next page</a></div>\n";

    echo "  <select name='page'>\n";
    for ($x=1; $x<=($this->count / $perPage) + 1; $x++)
      printf("    <option value='%d'%s>%d</option>\n",$x,$x==$this->page?" selected='selected'":"",$x);
    echo "  </select>\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
    return $s;
  }
};

class PouetBoxProdlistSelectors extends PouetBox
{
  function Load() {
    $row = SQLLib::selectRow("DESC prods type");
    preg_match_all("/'([a-zA-Z0-9\s_\-]+)'/",$row->Type,$m);
    $this->types = $m[1];
  }
  function Render() {
    global $PLATFORMS;
    echo "<table id='pouetbox_prodlist_selector' class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th colspan='2'>selection</th>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo "  <td>\n";
    echo "  type :\n";
    echo "  <select name='type[]' multiple='multiple' size='10'>\n";
    if (!$_GET["type"]) $_GET["type"] = array();
	  foreach($this->types as $v)
	    echo "  <option".(array_search($v,$_GET["type"])===false?"":" selected='selected'").">".$v."</option>\n";
    echo "  </select>\n";
    echo "  </td>\n";

    echo "  <td>\n";
    echo "  platform :\n";
    echo "  <select name='platform[]' multiple='multiple' size='10'>\n";
    if (!$_GET["platform"]) $_GET["platform"] = array();
    $plat = array();
	  foreach($PLATFORMS as $v) $plat[] = $v["name"];
	  usort($plat,"strcasecmp");
	  foreach($plat as $v)
	    echo "  <option".(array_search($v,$_GET["platform"])===false?"":" selected='selected'").">".$v."</option>\n";
    echo "  </select>\n";
    echo "  </td>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo "  <td class='foot' colspan='2'><input type='submit' value='Submit'/></td>\n";
    echo "</tr>\n";
    echo "</table>\n";
  }
};

///////////////////////////////////////////////////////////////////////////////

$q = new PouetBoxProdlistSelectors();
$q->Load();

$p = new PouetBoxProdlist();
$p->Load();
$TITLE = "prodlist";
if ($p->page > 1)
  $TITLE .= " :: page ".(int)$p->page;


require_once("include_pouet/header.php");
require_once("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo "<form action='prodlist.php' method='get'>\n";

foreach($_GET as $k=>$v)
  if ($k != "type" && $k != "platform" && $k != "page")
    echo "<input type='hidden' name='"._html($k)."' value='"._html($v)."'/>\n";

if($q) $q->Render();
if($p) $p->Render();
echo "</form>\n";
echo "</div>\n";

require_once("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
