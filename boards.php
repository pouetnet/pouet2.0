<?
include_once("bootstrap.inc.php");

class PouetBoxBoardMain extends PouetBox
{
  var $id;
  var $group;

  function PouetBoxBoardMain($id) {
    parent::__construct();
    $this->uniqueID = "pouetbox_boardmain";
    $this->id = (int)$id;

  }

  function LoadFromDB() {
    $this->board = SQLLib::SelectRow(sprintf_esc("select * from boards where id = %d",$this->id));

    $a = SQLLib::SelectRows(sprintf_esc("select * from boards_platforms where board = %d",$this->id));
    $this->platforms = array();
    foreach($a as $v) $this->platforms[] = $v->platform;

    $this->addedUser = PouetUser::Spawn($this->board->adder);

    $this->nfos = SQLLib::SelectRows(sprintf_esc("select * from othernfos where refid = %d",$this->id));

    $s = new BM_Query("affiliatedboards");
    $s->AddField("affiliatedboards.type");
    $s->Attach(array("affiliatedboards"=>"group"),array("groups as group"=>"id"));
    $s->AddWhere(sprintf_esc("affiliatedboards.board = %d",$this->id));
    $this->groups = $s->perform();

    $s = new BM_Query("prods");
    $s->AddWhere(sprintf_esc("prods.boardID = %d",$this->id));
    $this->bbstros = $s->perform();
  }

  function Render()
  {
    global $currentUser,$PLATFORMS;
    echo "<div id='".$this->uniqueID."' class='pouettbl'>\n";
    echo "<div id='boardname'>\n";
    echo sprintf("<a href='boards.php?which=%d'>%s</a>",$this->id,_html($this->board->name));

    if ($currentUser && $currentUser->CanEditItems())
    {
      printf("<div id='adminlinks'>");
      //printf("[<a href='admin_board_edit.php?which=%d' class='adminlink'>edit</a>]\n",$this->id);
      printf("</div>");
    }
    echo "</div>\n";

    echo "<div id='body'>\n";
    echo "  <div>\n";

    echo "    <table>\n";
    echo "      <tr>\n";
    echo "        <td>sysop :</td>\n";
    echo "        <td>"._html($this->board->sysop)."</td>\n";
    echo "      </tr>\n";
    echo "      <tr>\n";
    echo "        <td>number :</td>\n";
    echo "        <td>"._html($this->board->phonenumber)."</td>\n";
    echo "      </tr>\n";
    echo "      <tr>\n";
    echo "        <td>platforms :</td>\n";
    echo "        <td>";

    echo "<ul>";
    foreach($this->platforms as $t)
      echo "<li><a href='prodlist.php?platform[]=".rawurlencode($PLATFORMS[$t]["name"])."'><span class='platform os_".$PLATFORMS[$t]["slug"]."'>".$PLATFORMS[$t]["name"]."</span> ".$PLATFORMS[$t]["name"]."</a></li>\n";
    echo "</ul>";

    echo "</td>\n";
    echo "      </tr>\n";

    if ($this->nfos)
    {
      echo "      <tr>\n";
      echo "        <td>nfos :</td>\n";
      echo "        <td>";

      $a = array(); $i = 1;
      foreach($this->nfos as $t)
        $a[] = sprintf("<a href='board_nfo.php?which=%d'>%d</a>",$t->id,$i++);
      echo implode($a," ");

      echo "</td>\n";
      echo "      </tr>\n";
    }
    echo "    </table>\n";

    echo "  </div>\n";
    echo "  <div>\n";
    echo "    <table>\n";
    if ($this->bbstros)
    {
      echo "      <tr>\n";
      echo "        <td>bbstros :</td>\n";
      echo "        <td>";

      echo "<ul>";
      foreach($this->bbstros as $p)
        echo "<li>".$p->RenderLink()." by ".$p->RenderGroupsLong()."</li>\n";
      echo "</ul>";

      echo "</td>\n";
      echo "      </tr>\n";
    }
    if ($this->groups)
    {
      echo "      <tr>\n";
      echo "        <td>affiliations :</td>\n";
      echo "        <td>";

      echo "<ul>";
      foreach($this->groups as $g)
        echo "<li>".$g->group->RenderLong()." ".$g->type."</li>\n";
      echo "</ul>";

      echo "</td>\n";
      echo "      </tr>\n";
    }
    echo "    </table>\n";
    echo "  </div>\n";
    echo "</div>\n";

    echo " <div class='foot'>added on the ".$this->board->added." by ".$this->addedUser->PrintLinkedName()." ".$this->addedUser->PrintLinkedAvatar()."</div>\n";

    echo "</div>\n";
/*
    echo "<table id='pouetbox_groupmain' class='boxtable pagedtable'>\n";
    echo "<tr>\n";
    echo "<th colspan='9' id='groupname'>\n";
    echo sprintf("<a href='groups.php?which=%d'>%s",$this->id,_html($this->group->name));
    if ($this->group->acronym)
      echo sprintf(" [%s]",$this->group->acronym);
    echo "</a>";
    if ($this->group->web)
      echo sprintf(" [<a href='%s'>web</a>]",_html($this->group->web));
    if ($this->group->csdb)
      echo sprintf(" [<a href='http://csdb.dk/group/?id=%d'>csdb</a>]",$this->group->csdb);
    if ($this->group->zxdemo)
      echo sprintf(" [<a href='http://zxdemo.org/author.php?id=%d'>zxdemo</a>]",$this->group->zxdemo);

    printf(" [<a href='gloperator_log.php?which=%d&amp;what=group'>glöplog</a>]\n",$this->group->id);

    if ($currentUser && $currentUser->CanEditItems())
    {
      printf("<div id='adminlinks'>");
      printf("[<a href='admin_group_edit.php?which=%d' class='adminlink'>edit</a>]\n",$this->id);
      printf("</div>");
    }

    echo "</th>\n";
    echo "</tr>\n";

    $headers = array(
      "type"=>"type",
      "name"=>"prodname",
      "party"=>"release party",
      "release"=>"release date",
      "thumbup"=>"<img src='http://www.pouet.net/gfx/rulez.gif' alt='rulez' />",
      "thumbpig"=>"<img src='http://www.pouet.net/gfx/isok.gif' alt='piggie' />",
      "thumbdown"=>"<img src='http://www.pouet.net/gfx/sucks.gif' alt='sucks' />",
      "avg"=>"avg",
      "views"=>"popularity",
      "latestcomment"=>"last comment",
    );

    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a href='%s' class='%s%s' id='%s'>%s</a></th>\n",
        $this->BuildURL(array("order"=>$key)),$_GET["order"]==$key?"selected":"",($_GET["order"]==$key && $_GET["reverse"])?" reverse":"","sort_".$key,$text);
      if ($key == "type") $out = str_replace("</th>","",$out);
      if ($key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a id='%s' href='groups.php?which=%d&amp;order=%s'>%s</a></th>\n","sort_".$key,$this->id,$key,$text);
      if ($key == "type") $out = str_replace("</th>","",$out);
      if ($key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    foreach ($this->prods as $p) {
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
      echo $p->RenderAwards();
      echo "</td>\n";

      echo "<td>\n";
      if ($p->placings)
        echo $p->placings[0]->PrintResult($p->year);
      echo "</td>\n";

      echo "<td class='date'>".$p->RenderReleaseDate()."</td>\n";

      echo "<td class='votes'>".$p->voteup."</td>\n";
      echo "<td class='votes'>".$p->votepig."</td>\n";
      echo "<td class='votes'>".$p->votedown."</td>\n";

      $i = "isok";
      if ($p->voteavg < 0) $i = "sucks";
      if ($p->voteavg > 0) $i = "rulez";
      echo "<td class='votes'>".sprintf("%.2f",$p->voteavg)."&nbsp;<img src='http://www.pouet.net/gfx/".$i.".gif' alt='".$i."' /></td>\n";

      $pop = (int)($p->views * 100 / $this->maxviews);
      echo "<td><div class='innerbar_solo' style='width: ".$pop."px'>&nbsp;<span>".$pop."%</span></div></td>\n";

      if ($p->user)
        echo "<td>".$p->lastcomment." ".$p->user->PrintLinkedAvatar()."</td>\n";
      else
        echo "<td> </td>";

      echo "</tr>\n";
    }
    echo "<tr>\n";
    echo " <td class='foot' colspan='9'>added on the ".$this->group->quand." by ".$this->group->addeduser->PrintLinkedName()." ".$this->group->addeduser->PrintLinkedAvatar()."</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
*/
    return $s;
  }
};

class PouetBoxBoardList extends PouetBox
{
  var $letter;
  function PouetBoxBoardList($letter) {
    parent::__construct();
    $this->uniqueID = "pouetbox_boardlist";

    $letter = substr($letter,0,1);
    if (preg_match("/^[a-z]$/",$letter))
      $this->letter = $letter;
    else
      $this->letter = "#";

    $a = array();
    $a[] = "<a href='boards.php?pattern=%23'>#</a>";
    for($x=ord("a");$x<=ord("z");$x++)
      $a[] = sprintf("<a href='boards.php?pattern=%s'>%s</a>",chr($x),chr($x));

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

  function Load() {
    $s = new BM_query("boards");
    $s->AddField("boards.id");
    $s->AddField("boards.name");
    $s->AddField("boards.phonenumber");
    if ($this->letter=="#")
      $s->AddWhere(sprintf_esc("name regexp '^[^a-z]'"));
    else
      $s->AddWhere(sprintf_esc("name like '%s%%'",$this->letter));
    $s->AddOrder("name");
    $this->boards = $s->perform();
    /*
    if ($this->groups)
    {
      $ids = array();
      foreach($this->groups as $group) $ids[] = $group->id;

      $idstr = implode(",",$ids);

      $prods = SQLLib::selectRows(sprintf("select id,name,type,group1,group2,group3 from prods where (group1 in (%s)) or (group2 in (%s)) or (group3 in (%s))",$idstr,$idstr,$idstr));
      foreach($prods as $prod)
      {
        if ($prod->group1) $this->prods[$prod->group1][$prod->id] = $prod;
        if ($prod->group2) $this->prods[$prod->group2][$prod->id] = $prod;
        if ($prod->group3) $this->prods[$prod->group3][$prod->id] = $prod;
      }
    }*/

  }

  function RenderBody() {
    global $thread_categories;
    echo "<table class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th>name</th>\n";
    echo "  <th>countrycode</th>\n";
    echo "</tr>\n";
    foreach ($this->boards as $b) {
      echo "<tr>\n";
      echo "  <td class='boardname'><a href='boards.php?which=".(int)$b->id."'>"._html($b->name)."</a></td>\n";
      echo "  <td>"._html($b->phonenumber)."</td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};
///////////////////////////////////////////////////////////////////////////////

$boardID = (int)$_GET["which"];

$p = null;
if (!$boardID)
{
  $pattern = $_GET["pattern"] ? $_GET["pattern"] : chr(rand(ord("a"),ord("z")));
  $p = new PouetBoxBoardList($pattern);
  $p->Load();
  $TITLE = "boards: ".$p->letter;
}
else
{
  $p = new PouetBoxBoardMain($boardID);
  $p->Load();
  $TITLE = $p->board->name;
}

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
if($p) $p->Render();
echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>
