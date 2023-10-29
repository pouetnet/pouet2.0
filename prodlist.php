<?php
require_once("bootstrap.inc.php");

class PouetBoxProdlist extends PouetBox
{
  public $id;
  public $group;
  public $perPage;
  public $page;
  public $prods;
  public $count;

  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodlist";
  }

  function LoadFromDB()
  {
    $s = new SQLSelect();

    $this->perPage = get_setting("prodlistprods");

    $this->page = (int)max( 1, (int)@$_GET["page"] );

    $s = new BM_Query("prods");

    // QUERYNAUGHT INCOMING

    if (is_array(@$_GET["type"]))
    {
      $cond = array();
      foreach($_GET["type"] as $type)
        $cond[] = sprintf_esc("FIND_IN_SET('%s',prods.type)",$type);
      $s->AddWhere(implode(" OR ",$cond));
    }
    if (is_array(@$_GET["platform"]))
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
    if (is_array(@$_GET["group"]))
    {
      foreach($_GET["group"] as $v) if ($v)
      {
        $s->AddWhere(sprintf_esc("(prods.group1 = %d OR prods.group2 = %d OR prods.group3 = %d)",$v,$v,$v));
      }
    }
    if (@$_GET["releaseDateFrom"])
    {
      $s->AddWhere(sprintf_esc("prods.releaseDate >= '%s'",$_GET["releaseDateFrom"]));
    }
    if (@$_GET["releaseDateUntil"])
    {
      $s->AddWhere(sprintf_esc("prods.releaseDate <= '%s'",$_GET["releaseDateUntil"]));
    }
    if (@$_GET["addedDateFrom"])
    {
      $s->AddWhere(sprintf_esc("prods.addedDate >= '%s'",$_GET["addedDateFrom"]));
    }
    if (@$_GET["addedDateUntil"])
    {
      $s->AddWhere(sprintf_esc("prods.addedDate <= '%s'",$_GET["addedDateUntil"]));
    }
    if (@$_GET["party"])
    {
      $s->AddWhere(sprintf_esc("party = %d",$_GET["party"]));
    }
    if (@$_GET["partyYear"])
    {
      $s->AddWhere(sprintf_esc("party_year = %d",$_GET["partyYear"]));
    }
    if (@$_GET["partyRank"])
    {
      $s->AddWhere(sprintf_esc("party_place = %d",$_GET["partyRank"]));
    }
    if (@$_GET["partyRankHigher"])
    {
      $s->AddWhere(sprintf_esc("party_place <= %d",$_GET["partyRankHigher"]));
    }
    if (@$_GET["partyRankLower"])
    {
      $s->AddWhere(sprintf_esc("party_place >= %d",$_GET["partyRankLower"]));
    }

    $dir = "DESC";
    if (@$_GET["reverse"])
      $dir = "ASC";
    switch(@$_GET["order"])
    {
      case "type": $s->AddOrder("prods.type ".$dir); break;
      case "name": $s->AddOrder("prods.name ".$dir); break;
      case "group": $s->AddOrder("prods.group1 ".$dir); $s->AddOrder("prods.group2 ".$dir); $s->AddOrder("prods.group3 ".$dir); break;
      case "party": $s->AddOrder("prods_party.name ".$dir); $s->AddOrder("prods.party_year ".$dir); $s->AddOrder("prods.party_place ".$dir); break;
      case "thumbup": $s->AddOrder("prods.voteup ".$dir); break;
      case "thumbpig": $s->AddOrder("prods.votepig ".$dir); break;
      case "thumbdown": $s->AddOrder("prods.votedown ".$dir); break;
      case "thumbdiff": $s->AddOrder("(prods.voteup - prods.votedown) ".$dir); break;
      case "avg": $s->AddOrder("prods.voteavg ".$dir); break;
      case "views": $s->AddOrder("prods.views ".$dir); break;
      case "added": $s->AddOrder("prods.addedDate ".$dir); break;
      case "random": $s->AddOrder("RAND()"); break;
    }
    $s->AddOrder("prods.releaseDate ".$dir);
    $s->AddOrder("prods.addedDate ".$dir);

    $s->SetLimit( $this->perPage, (int)(($this->page-1) * $this->perPage) );

    //echo $s->GetQuery();

    $this->prods = $s->performWithCalcRows( $this->count );
    PouetCollectPlatforms($this->prods);
    PouetCollectAwards($this->prods);
  }

  function Render()
  {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "type"=>"type",
      "name"=>"prodname",
      "platform"=>"platform",
      "group"=>"group",
      "party"=>"release party",
      "release"=>"release date",
      "added"=>"added",
      "thumbup"=>"<span class='rulez' title='rulez'>rulez</span>",
      "thumbpig"=>"<span class='isok' title='piggie'>piggie</span>",
      "thumbdown"=>"<span class='sucks' title='sucks'>sucks</span>",
      "avg"=>"avg",
      "views"=>"popularity",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a href='%s' class='%s%s %s'>%s</a></th>\n",
        adjust_query_header(array("order"=>$key)),@$_GET["order"]==$key?"selected":"",(@$_GET["order"]==$key && @$_GET["reverse"])?" reverse":"","sort_".$key,$text);
      if ($key == "type" || $key == "name") $out = str_replace("</th>","",$out);
      if ($key == "platform" || $key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    foreach ($this->prods as $p)
    {
      echo "<tr>\n";

      echo "<td>\n";
      echo $p->RenderTypeIcons();
      echo $p->RenderPlatformIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo $p->RenderAccolades();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      if ($p->placings)
        echo $p->placings[0]->PrintResult();
      echo "</td>\n";

      echo "<td class='date'>".$p->RenderReleaseDate()."</td>\n";
      echo "<td class='date'>".$p->RenderAddedDate()."</td>\n";

      echo "<td class='votes'>".$p->voteup."</td>\n";
      echo "<td class='votes'>".$p->votepig."</td>\n";
      echo "<td class='votes'>".$p->votedown."</td>\n";
      echo "<td class='votesavg'>".$p->RenderAvg()."</td>\n";

      $pop = (int)calculate_popularity( $p->views );
      echo "<td>".progress_bar_solo( $pop, $pop."%" )."</td>\n";

      echo "</tr>\n";
    }

    echo "<tr>\n";
    echo "<td class='nav' colspan=".(count($headers)-2).">\n";

    $numPages = ceil($this->count / $this->perPage);
    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='".adjust_query(array("page"=>($this->page - 1)))."'>previous page</a></div>\n";
    if ($this->page < $numPages)
      echo "  <div class='nextpage'><a href='".adjust_query(array("page"=>($this->page + 1)))."'>next page</a></div>\n";

    echo "  <select name='page'>\n";
    for ($x = 1; $x <= $numPages; $x++)
      printf("    <option value='%d'%s>%d</option>\n",$x,$x==$this->page?" selected='selected'":"",$x);
    echo "  </select>\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
  }
};

class PouetBoxProdlistSelectors extends PouetBox
{
  public $types;
  function Load()
  {
    $row = SQLLib::selectRow("DESC prods type");
    $this->types = enum2array($row->Type);
  }
  function Render()
  {
    global $PLATFORMS;
    echo "<table id='pouetbox_prodlist_selector' class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th colspan='2'>selection</th>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo "  <td>\n";
    echo "  type :\n";
    echo "  <select name='type[]' multiple='multiple' size='10'>\n";
    if (!@$_GET["type"]) $_GET["type"] = array();
	  foreach($this->types as $v)
	    echo "  <option".((!is_array($_GET["type"])||array_search($v,$_GET["type"])===false)?"":" selected='selected'").">".$v."</option>\n";
    echo "  </select>\n";
    echo "  </td>\n";

    echo "  <td>\n";
    echo "  platform :\n";
    echo "  <select name='platform[]' multiple='multiple' size='10'>\n";
    if (!@$_GET["platform"]) $_GET["platform"] = array();
    $plat = array();
	  foreach($PLATFORMS as $v) $plat[] = $v["name"];
	  usort($plat,"strcasecmp");
	  foreach($plat as $v)
	    echo "  <option".((!is_array($_GET["platform"])||array_search($v,$_GET["platform"])===false)?"":" selected='selected'").">".$v."</option>\n";
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
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo "<form action='prodlist.php' method='get'>\n";

foreach($_GET as $k=>$v)
{
  if ($k != "page" && $k != "type" && $k != "platform") // hidden fields only
  {
    if (is_array($v))
    {
      foreach($v as $k2=>$v2)
      echo "<input type='hidden' name='"._html($k)."[]' value='"._html($v2)."'/>\n";
    }
    else
    {
      echo "<input type='hidden' name='"._html($k)."' value='"._html($v)."'/>\n";
    }
  }
}
if($q) $q->Render();
if($p) $p->Render();
echo "</form>\n";
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
