<?php
require_once("bootstrap.inc.php");

class PouetBoxPartyHeader extends PouetBox
{
  public $party;
  public $partylinks;
  public $year;
  public $years;
  function __construct( $partyView )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_partyheader";

    $this->party = $partyView->party;
    $this->year = $partyView->year;

    $this->title = _html($this->party->name." ".$this->year);
  }

  function LoadFromDB()
  {
    $this->partylinks = SQLLib::selectRow(sprintf("SELECT * FROM `partylinks` WHERE party = %d and year = %d",$this->party->id,$this->year));

    $this->years = array();

    $rows = SQLLib::selectRows(sprintf("SELECT party_year FROM prods WHERE party = %d GROUP BY party_year",$this->party->id));
    foreach($rows as $v)
      $this->years[$v->party_year] = true;
    $rows = SQLLib::selectRows(sprintf("SELECT invitationyear FROM prods WHERE invitation = %d GROUP BY invitationyear",$this->party->id));
    foreach($rows as $v)
      $this->years[$v->invitationyear] = true;
    ksort($this->years);
  }

  function RenderContent()
  {
    global $currentUser;

    if ($this->party->web)
      printf("[<a href='%s'>web</a>]\n",_html($this->party->web));
    if(file_exists($this->party->GetResultsLocalFileName($this->year)))
      echo $this->party->RenderResultsLink( $this->year );
    else if ($currentUser && $currentUser->CanSubmitItems())
      printf(" [<a class='submitadditional' href='submit_party_edition_info.php?which=%d&amp;when=%d'>+results</a>]\n",$this->party->id,$this->year);

    if($this->partylinks && $this->partylinks->download)
      echo "[<a href='".$this->partylinks->download."'>download</a>]\n";
    else if ($currentUser && $currentUser->CanSubmitItems())
      printf(" [<a class='submitadditional' href='submit_party_edition_info.php?which=%d&amp;when=%d'>+download</a>]\n",$this->party->id,$this->year);

    if($this->partylinks && $this->partylinks->slengpung)
      echo " [<a href='http://www.slengpung.com/?eventid=".(int)$this->partylinks->slengpung."'>slengpung</a>]";
    else if ($currentUser && $currentUser->CanSubmitItems())
      printf(" [<a class='submitadditional' href='submit_party_edition_info.php?which=%d&amp;when=%d'>+slengpung</a>]\n",$this->party->id,$this->year);

    if($this->partylinks && $this->partylinks->csdb)
      echo " [<a href='http://csdb.dk/event/?id=".(int)$this->partylinks->csdb."'>csdb</a>]";
    else if ($currentUser && $currentUser->CanSubmitItems())
      printf(" [<a class='submitadditional' href='submit_party_edition_info.php?which=%d&amp;when=%d'>+csdb</a>]\n",$this->party->id,$this->year);

    if($this->partylinks && $this->partylinks->zxdemo)
      echo " [<a href='http://zxdemo.org/party.php?id=".(int)$this->partylinks->zxdemo."'>zxdemo</a>]";
    //else if ($currentUser && $currentUser->CanSubmitItems())
    //  printf(" [<a class='submitadditional' href='submit_party_edition_info.php?which=%d&amp;when=%d'>+zxdemo</a>]\n",$this->party->id,$this->year);

    if($this->partylinks && $this->partylinks->demozoo)
      echo " [<a href='http://demozoo.org/parties/".(int)$this->partylinks->demozoo."/'>demozoo</a>]";
    else if ($currentUser && $currentUser->CanSubmitItems())
      printf(" [<a class='submitadditional' href='submit_party_edition_info.php?which=%d&amp;when=%d'>+demozoo</a>]\n",$this->party->id,$this->year);

    if($this->partylinks && $this->partylinks->artcity)
      echo " [<a href='http://artcity.bitfellas.org/index.php?a=search&type=tag&text=".rawurlencode($this->partylinks->artcity)."'>artcity</a>]";
    else if ($currentUser && $currentUser->CanSubmitItems())
      printf(" [<a class='submitadditional' href='submit_party_edition_info.php?which=%d&amp;when=%d'>+artcity</a>]\n",$this->party->id,$this->year);

    if ($currentUser && $currentUser->CanEditItems())
    {
      printf(" [<a href='admin_party_edit.php?which=%d' class='adminlink'>edit</a>]\n",$this->party->id);
      printf(" [<a href='admin_party_edition_edit.php?which=%d&amp;when=%d' class='adminlink'>edit year</a>]\n",$this->party->id,$this->year);
    }
    printf(" [<a href='gloperator_log.php?which=%d&amp;what=party'>glöplog</a>]\n",$this->party->id);

  }

  function RenderFooter()
  {
    $y = array();
    foreach($this->years as $v=>$dummy)
      $y[] = "<a href='party.php?which=".rawurlencode($this->party->id)."&amp;when=".$v."'>".$v."</a>";
    echo "  <div class='yearselect'>".implode(" |\n",$y)."</div>\n";
    echo "  <div class='foot'>added on the ".$this->party->addedDate." by ".$this->party->addedUser->PrintLinkedName()." ".$this->party->addedUser->PrintLinkedAvatar()."</div>\n";
    echo "</div>\n";
  }
};

class PouetBoxPartyView extends PouetBox
{
  public $party;
  public $year;
  public $prods;
  public $sortByCompo;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_partyview";
  }

  function LoadFromDB()
  {
    $this->party = PouetParty::spawn($_GET["which"]);
    if (!$this->party) return;

    $this->party->addedUser = PouetUser::spawn( $this->party->addedUser );

    if (isset($_GET["when"]))
    {
      $this->year = $_GET["when"];
    }
    else
    {
      $r = SQLLib::selectRow(sprintf_esc("select party_year from prods where party = %d order by rand() limit 1",$_GET["which"]));
      if (!$r)
      {
        $r = SQLLib::selectRow(sprintf_esc("select invitationyear as party_year from prods where invitation = %d order by rand() limit 1",$_GET["which"]));
      }
      $this->year = $r ? $r->party_year : 0;
    }

    if ($this->year < 100)
    {
      $this->year += ($this->year < 50 ? 2000 : 1900);
    }

    $this->prods = array();
    $s = new BM_Query("prods");
    $s->AddWhere( sprintf_esc("(prods.party = %d AND prods.party_year = %d) or (prodotherparty.party = %d AND prodotherparty.party_year = %d)",$this->party->id,$this->year,$this->party->id,$this->year) );

    // this is where it gets nasty; luckily we can fake it relatively elegantly: ORM won't notice if we override some of the field selections
    $s->AddJoin("left","prodotherparty",sprintf_esc("prodotherparty.prod = prods.id and (prodotherparty.party = %d AND prodotherparty.party_year = %d)",$this->party->id,$this->year));
    foreach($s->GetFields() as &$v)
    {
      if ($v == "prods.party_compo as prods_party_compo")
      {
        $v = "COALESCE(prodotherparty.party_compo,prods.party_compo) as prods_party_compo";
      }
      if ($v == "prods.party_place as prods_party_place")
      {
        $v = "COALESCE(prodotherparty.party_place,prods.party_place) as prods_party_place";
      }
    }

    $dir = "DESC";
    if (@$_GET["reverse"])
      $dir = "ASC";
    $this->sortByCompo = false;
    switch(@$_GET["order"])
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
      default:
      {
        $s->AddOrder( "COALESCE(prodotherparty.party_compo,prods.party_compo)" );
        $s->AddOrder( "COALESCE(prodotherparty.party_place,prods.party_place)" );
        $s->AddOrder( "prods.name" );
        $s->AddOrder( "prods.id" );
        $this->sortByCompo = true;

        // include invitations on top
        $inv = new BM_Query("prods");
        $inv->AddWhere( sprintf_esc("(prods.invitation = %d AND prods.invitationyear = %d)",$this->party->id,$this->year,$this->party->id,$this->year) );
        $inv->AddOrder( "prods.addedDate" );
        $prods = $inv->perform();
        foreach($prods as &$v)
        {
          $v->party_compo = 1; // invit
          $v->placings = array();
        }

        $this->prods = array_merge( $this->prods, $prods );
      } break;
    }
    $prods = $s->perform();
    $this->prods = array_merge( $this->prods, $prods );
    PouetCollectPlatforms($this->prods);
    PouetCollectAwards($this->prods);
  }

  function Render()
  {
    echo "<table id='".$this->uniqueID."' class='boxtable'>\n";

    $headers = array(
      "compo"=>"compo",
      "type"=>"type",
      "name"=>"prodname",
/*
      "platform"=>"platform",
      "group"=>"group",
      "party"=>"release party",
      "release"=>"release",
      "added"=>"added",
*/
      "thumbup"=>"<span class='rulez' title='rulez'>rulez</span>",
      "thumbpig"=>"<span class='isok' title='piggie'>piggie</span>",
      "thumbdown"=>"<span class='sucks' title='sucks'>sucks</span>",
      "avg"=>"avg",
      "views"=>"popularity",
    );

    $lastCompo = "*";
    $headerDone = false;
    global $COMPOTYPES;
    foreach($this->prods as $p)
    {
      if ($p->party_compo != $lastCompo && !$headerDone)
      {
        echo "<tr class='sortable'>\n";
        foreach($headers as $key=>$text)
        {
          $out = sprintf("<th><a href='%s' class='%s%s %s'>%s</a></th>\n",
            adjust_query_header(array("order"=>$key)),@$_GET["order"]==$key?"selected":"",(@$_GET["order"]==$key && @$_GET["reverse"])?" reverse":"","sort_".$key,$text);
          if ($key == "type" || $key == "name") $out = str_replace("</th>","",$out);
          if ($key == "platform" || $key == "name") $out = str_replace("<th>"," ",$out);
          if ($key == "compo" && $this->sortByCompo && $p->party_compo && $COMPOTYPES[$p->party_compo]) $out = sprintf("<th id='%s'>%s</th>",hashify($COMPOTYPES[$p->party_compo]),$COMPOTYPES[$p->party_compo]);
          echo $out;
        }
        echo "</tr>\n";
        if (!$this->sortByCompo)
          $headerDone = true;
        $lastCompo = $p->party_compo;
      }
      echo "<tr>\n";
      echo "<td>\n";
      if (!$this->sortByCompo)
        echo @$COMPOTYPES[$p->party_compo]." ";
      if (@$p->placings[0])
        echo $p->placings[0]->PrintRanking();
      echo "</td>\n";
      echo "<td class='prod'>\n";
      echo $p->RenderTypeIcons();
      echo $p->RenderPlatformIcons();
      echo "".$p->RenderLink()." ";
      if ($p->groups)
        echo "by ".$p->RenderGroupsLong()."\n";
      echo $p->RenderAccolades();
      echo "</td>\n";

      echo "<td class='votes'>".$p->voteup."</td>\n";
      echo "<td class='votes'>".$p->votepig."</td>\n";
      echo "<td class='votes'>".$p->votedown."</td>\n";
      echo "<td class='votesavg'>".$p->RenderAvg()."</td>\n";

      $pop = (int)calculate_popularity( $p->views );
      echo "<td>".progress_bar_solo( $pop, $pop."%" )."</td>\n";

      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};

class PouetBoxPartyLists extends PouetBox
{
  var $id;
  var $topic;
  var $posts;
  var $data;
  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_partylists";
    $this->title = "lists containing this party";
    $this->id = (int)$id;
  }

  function LoadFromDB()
  {
    $s = new BM_Query();
    $s->AddField("lists.id as id");
    $s->AddField("lists.name as name");
    $s->AddTable("list_items");
    $s->AddJoin("","lists","list_items.list=lists.id");
    $s->attach(array("lists"=>"owner"),array("users as user"=>"id"));
    $s->AddWhere("list_items.itemid=".$this->id);
    $s->AddWhere("list_items.type='party'");
    $s->AddOrder("lists.name");
    $this->data = $s->perform();
  }

  function RenderBody()
  {
    echo "<ul class='boxlist boxlisttable'>\n";
    foreach($this->data as $list)
    {
      echo "<li>\n";
      printf("  <span><a href='lists.php?which=%d'>%s</a></span>\n",$list->id,_html($list->name));
      echo "  <span>".$list->user->PrintLinkedAvatar()." ".$list->user->PrintLinkedName()."</span>\n";
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
};

///////////////////////////////////////////////////////////////////////////////

$partyBox = new PouetBoxPartyView();
$partyBox->Load();
if (!$partyBox->party)
{
  redirect("parties.php");
}
if (!$partyBox->prods && isset($_GET["when"]))
{
  redirect("party.php?which=".(int)$partyBox->party->id);
}

$TITLE = $partyBox->party->name." ".$partyBox->year;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$headerBox = new PouetBoxPartyHeader($partyBox);
$headerBox->Load();
$headerBox->Render();

$partyBox->Render();

$lists = new PouetBoxPartyLists((int)$partyBox->party->id);
$lists->Load();
if ($lists->data)
{
  $lists->Render();
}


echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
