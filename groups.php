<?php
require_once("bootstrap.inc.php");

class PouetBoxGroupMain extends PouetBox
{
  var $id;
  var $group;
  var $addedUser;
  var $prods;
  var $affil;

  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_groupmain";
    $this->id = (int)$id;
  }
  function LoadFromDB()
  {
    $s = new SQLSelect();

    $this->group = PouetGroup::Spawn($this->id);
    if (!$this->group)
    {
      return;
    }
    $this->addedUser = PouetUser::Spawn($this->group->addedUser);

    // not to boast or anything, but this is fucking beautiful.

    $sub = new SQLSelect();
    $sub->AddField("max(comments.addedDate) as maxDate");
    $sub->AddField("comments.which");
    $sub->AddTable("comments");
    $sub->AddJoin("left","prods","prods.id = comments.which");
    //$sub->AddOrder("comments.addedDate desc");
    $sub->AddGroup("comments.which");
    $sub->AddWhere(sprintf_esc("(prods.group1 = %d) or (prods.group2 = %d) or (prods.group3 = %d)",$this->id,$this->id,$this->id));

    $s = new BM_Query("prods");
    $s->AddField("cmts.addedDate as lastcomment");
    $s->AddField("cmts.rating as lastcommentrating");
    $s->AddJoin("left","(select comments.addedDate,comments.who,comments.which,comments.rating from (".$sub->GetQuery().") as dummy left join comments on dummy.maxDate = comments.addedDate and dummy.which = comments.which) as cmts","cmts.which=prods.id");
    $s->attach(array("cmts"=>"who"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("(prods.group1 = %d) or (prods.group2 = %d) or (prods.group3 = %d)",$this->id,$this->id,$this->id));

    $r = !!@$_GET["reverse"];
    switch(@$_GET["order"])
    {
      case "type": $s->AddOrder("prods.type ".($r?"DESC":"ASC")); break;
      case "platform": { $s->AddJoin("left","prods_platforms as pp","pp.prod=prods.id"); $s->AddOrder("pp.platform ".($r?"DESC":"ASC")); break; }
      case "party": $s->AddOrder("prods_party.name ".($r?"DESC":"ASC")); $s->AddOrder("prods.party_year ".($r?"DESC":"ASC")); $s->AddOrder("prods.party_place ".($r?"DESC":"ASC")); break;
      case "release": $s->AddOrder("prods.releaseDate ".($r?"ASC":"DESC")); break;
      case "thumbup": $s->AddOrder("prods.voteup ".($r?"ASC":"DESC")); break;
      case "thumbpig": $s->AddOrder("prods.votepig ".($r?"ASC":"DESC")); break;
      case "thumbdown": $s->AddOrder("prods.votedown ".($r?"ASC":"DESC")); break;
      case "avg": $s->AddOrder("prods.voteavg ".($r?"ASC":"DESC")); break;
      case "views": $s->AddOrder("prods.views ".($r?"ASC":"DESC")); break;
      case "latestcomment": $s->AddOrder("lastcomment ".($r?"ASC":"DESC")); break;
      default: $s->AddOrder("prods.name ".($r?"DESC":"ASC")); break;
    }
    $this->prods = $s->perform();
    PouetCollectPlatforms($this->prods);
    PouetCollectAwards($this->prods);

    $s = new BM_Query();
    $s->AddTable("affiliatedboards");
    $s->AddField("affiliatedboards.type");
    $s->attach(array("affiliatedboards"=>"board"),array("boards as board"=>"id"));
    $s->AddWhere(sprintf_esc("affiliatedboards.group=%d",$this->id));
    $this->affil = $s->perform();
  }

  function Render()
  {
    global $currentUser;

    echo "<table id='pouetbox_groupmain' class='boxtable pagedtable'>\n";
    echo "<tr>\n";
    echo "<th colspan='9' id='groupname'>\n";
    echo sprintf("<a href='groups.php?which=%d'>%s",$this->id,_html($this->group->name));
    if ($this->group->disambiguation)
      echo sprintf(" <small>(%s)</small>",$this->group->disambiguation);
    if ($this->group->acronym)
      echo sprintf(" [%s]",$this->group->acronym);
    echo "</a>";
    if ($this->group->web)
      echo sprintf(" [<a href='%s'>web</a>]",_html($this->group->web));
    if ($this->group->csdb)
      echo sprintf(" [<a href='http://csdb.dk/group/?id=%d'>csdb</a>]",$this->group->csdb);
    if ($this->group->zxdemo)
      echo sprintf(" [<a href='http://zxdemo.org/author.php?id=%d'>zxdemo</a>]",$this->group->zxdemo);
    if ($this->group->demozoo)
      echo sprintf(" [<a href='http://demozoo.org/groups/%d/'>demozoo</a>]",$this->group->demozoo);

    printf(" [<a href='gloperator_log.php?which=%d&amp;what=group'>glöplog</a>]\n",$this->group->id);

    printf("<div id='adminlinks'>");
    if ($currentUser && $currentUser->CanEditItems())
    {
      printf("[<a href='admin_group_edit.php?which=%d' class='adminlink'>admin</a>]\n",$this->id);
    }
    if ($currentUser && $currentUser->CanSubmitItems())
    {
      printf("[<a href='submit_modification_request.php?group=%d'>edit</a>]\n",$this->id);
    }
    printf("</div>");

    echo "</th>\n";
    echo "</tr>\n";

    $headers = array(
      "type"=>"type",
      "name"=>"prodname",
      "platform"=>"platform",
      "party"=>"release party",
      "release"=>"release date",
      "thumbup"=>"<span class='rulez' title='rulez'>rulez</span>",
      "thumbpig"=>"<span class='isok' title='piggie'>piggie</span>",
      "thumbdown"=>"<span class='sucks' title='sucks'>sucks</span>",
      "avg"=>"avg",
      "views"=>"popularity",
      "latestcomment"=>"last comment",
    );

    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a href='%s' class='%s%s %s'>%s</a></th>\n",
        adjust_query_header(array("order"=>$key)),@$_GET["order"]==$key?"selected":"",(@$_GET["order"]==$key && @$_GET["reverse"])?" reverse":"","sort_".$key,$text);
      if ($key == "type") $out = str_replace("</th>","",$out);
      if ($key == "name") { $out = str_replace("<th>"," ",$out); $out = str_replace("</th>"," ",$out); }
      if ($key == "platform") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

/*
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a id='%s' href='groups.php?which=%d&amp;order=%s'>%s</a></th>\n","sort_".$key,$this->id,$key,$text);
      if ($key == "type") $out = str_replace("</th>","",$out);
      if ($key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";
*/

    foreach ($this->prods as $p)
    {
      echo "<tr>\n";

      echo "<td>\n";
      echo $p->RenderTypeIcons();
      echo $p->RenderPlatformIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      $groups = $p->groups;
      foreach($groups as $k=>$g) if ($g->id == $this->id) unset($groups[$k]);
      if ($groups)
      {
        $a = array();
        foreach($groups as $g) $a[] = $g->RenderShort();
        echo " (with ".implode(", ",$a).")";
      }
      echo $p->RenderAccolades();
      echo "</td>\n";

      echo "<td>\n";
      if ($p->placings)
        echo $p->placings[0]->PrintResult();
      echo "</td>\n";

      echo "<td class='date'>".$p->RenderReleaseDate()."</td>\n";

      echo "<td class='votes'>".$p->voteup."</td>\n";
      echo "<td class='votes'>".$p->votepig."</td>\n";
      echo "<td class='votes'>".$p->votedown."</td>\n";
      echo "<td class='votesavg'>".$p->RenderAvg()."</td>\n";

      $pop = (int)calculate_popularity( $p->views );
      echo "<td>".progress_bar_solo( $pop, $pop."%" )."</td>\n";

      if ($p->user)
      {
        $rating = "isok";
        if ($p->lastcommentrating < 0) $rating = "sucks";
        if ($p->lastcommentrating > 0) $rating = "rulez";
        echo "<td>";
        echo "<span class='vote ".$rating."'>".$rating."</span> ";
        echo $p->lastcomment." ".$p->user->PrintLinkedAvatar()."</td>\n";
      }
      else
        echo "<td>&nbsp;</td>";

      echo "</tr>\n";
    }
    if ($this->affil)
    {
      echo "<tr>\n";
      echo " <td colspan='9' class='affil'>";
      echo " <ul>\n";
      foreach($this->affil as $v)
        echo sprintf("<li><a href='boards.php?which=%d'>%s</a> (%s)</li>",$v->board->id,_html($v->board->name),_html($v->type));
      echo " </ul>\n";
      echo " </td>\n";
      echo "</tr>\n";
    }
    echo "<tr>\n";
    echo " <td class='foot' colspan='9'>added on the ".$this->group->addedDate." by ".($this->addedUser?$this->addedUser->PrintLinkedName():"")." ".($this->addedUser?$this->addedUser->PrintLinkedAvatar():"")."</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
  }
};

class PouetBoxGroupLists extends PouetBox
{
  public $id;
  public $topic;
  public $posts;
  public $data;
  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_grouplists";
    $this->title = "lists containing this group";
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
    $s->AddWhere("list_items.type='group'");
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

class PouetBoxGroupList extends PouetBox
{
  public $letter;
  public $letterselect;
  public $groups;
  public $prods;
  function __construct($letter)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_grouplist";

    $letter = substr($letter,0,1);
    if (preg_match("/^[a-z]$/",$letter))
      $this->letter = $letter;
    else
      $this->letter = "#";

    $a = array();
    $a[] = "<a href='groups.php?pattern=%23'>#</a>";
    for($x=ord("a");$x<=ord("z");$x++)
      $a[] = sprintf("<a href='groups.php?pattern=%s'>%s</a>",chr($x),chr($x));

    $this->letterselect = "[ ".implode(" |\n",$a)." ]";
  }

  function RenderHeader()
  {
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    echo " <div class='letterselect'>".$this->letterselect."</div>\n";
  }

  function RenderFooter()
  {
    echo " <div class='letterselect'>".$this->letterselect."</div>\n";
    echo "</div>\n";
  }

  function Load()
  {
    $s = new BM_query("groups");
    if ($this->letter=="#")
      $s->AddWhere(sprintf("name regexp '^[^a-z]'"));
    else
      $s->AddWhere(sprintf("name like '%s%%'",$this->letter));
    $s->AddOrder("name");
    $this->groups = $s->perform();
    $this->prods = array();
    if ($this->groups)
    {
      $ids = array();
      foreach($this->groups as $group) $ids[] = $group->id;

      $idstr = implode(",",$ids);

      for ($x = 1; $x <= 3; $x++)
      {
        $counts = SQLLib::selectRows(sprintf("select group".$x." as groupID, count(*) as c from prods where group".$x." in (%s) group by group".$x."",$idstr));
        foreach($counts as $count)
        {
          if (@!$this->prods[$count->groupID])
          {
            $this->prods[$count->groupID] = 0;
          }
          $this->prods[$count->groupID] += $count->c;
        }
      }
    }

  }

  function RenderBody()
  {
    global $thread_categories;
    echo "<table class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th>groups</th>\n";
    echo "  <th>prods</th>\n";
    echo "</tr>\n";
    foreach ($this->groups as $r) {
      echo "<tr>\n";
      echo "  <td class='groupname'>";
      echo $r->RenderFull();
      if ($r->disambiguation)
        echo sprintf(" <small>(%s)</small>",$r->disambiguation);
      echo "</td>\n";
      echo "  <td>\n";
      echo (int)@$this->prods[$r->id];
      echo "  </td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};
///////////////////////////////////////////////////////////////////////////////

$groupID = (int)@$_GET["which"];

$listBox = null;
$groupBox = null;
if (!$groupID)
{
  $pattern = @$_GET["pattern"] ? @$_GET["pattern"] : chr(rand(ord("a"),ord("z")));
  $listBox = new PouetBoxGroupList($pattern);
  $listBox->Load();
  $TITLE = "groups: ".$listBox->letter;
}
else
{
  $groupBox = new PouetBoxGroupMain($groupID);
  $groupBox->Load();
  if (!$groupBox->group)
  {
    redirect("groups.php");
    exit();
  }

  $TITLE = $groupBox->group->name;
}

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if($listBox)
{
  $listBox->Render();
}
else
{
  $groupBox->Render();

  $lists = new PouetBoxGroupLists($groupID);
  $lists->Load();
  if ($lists->data)
  {
    $lists->Render();
  }
}

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
