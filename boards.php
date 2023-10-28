<?php
include_once("bootstrap.inc.php");

class PouetBoxBoardMain extends PouetBox
{
  public $id;
  public $group;
  public $board;
  public $groups;
  public $bbstros;
  public $platforms;
  public $nfos;

  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_boardmain";
    $this->id = (int)$id;

  }

  function LoadFromDB()
  {
    $this->board = PouetBoard::Spawn($this->id);
    if (!$this->board) return;

    $a = SQLLib::SelectRows(sprintf_esc("select * from boards_platforms where board = %d",$this->id));
    $this->platforms = array();
    foreach($a as $v) $this->platforms[] = $v->platform;

    $this->nfos = SQLLib::SelectRows(sprintf_esc("select * from othernfos where refid = %d",$this->id));

    $s = new BM_Query();
    $s->AddTable("affiliatedboards");
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
    if (!$this->board) return;
    global $currentUser,$PLATFORMS;
    echo "<div id='".$this->uniqueID."' class='pouettbl'>\n";
    echo "<div id='boardname'>\n";
    echo $this->board->RenderLink();

    if ($currentUser && $currentUser->CanEditItems())
    {
      printf(" [<a href='admin_board_edit.php?which=%d' class='adminlink'>edit</a>]\n",$this->id);
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
    if ($this->board->telnetip)
    {
      echo "      <tr>\n";
      echo "        <td>telnet address :</td>\n";
      $url = $this->board->telnetip;
      if (strstr($url,"://")===false)
        $url = "telnet://" . $url;
      echo "        <td><a href='"._html($url)."'>"._html($this->board->telnetip)."</a></td>\n";
      echo "      </tr>\n";
    }
    if ($date = renderHalfDate($this->board->started))
    {
      echo "      <tr>\n";
      echo "        <td>started :</td>\n";
      echo "        <td>".$date."</td>\n";
      echo "      </tr>\n";
    }
    if ($date = renderHalfDate($this->board->closed))
    {
      echo "      <tr>\n";
      echo "        <td>closed :</td>\n";
      echo "        <td>".$date."</td>\n";
      echo "      </tr>\n";
    }
    if ($this->platforms)
    {
      echo "      <tr>\n";
      echo "        <td>platforms :</td>\n";
      echo "        <td>";

      echo "<ul>";
      foreach($this->platforms as $t)
        echo "<li><a href='prodlist.php?platform[]=".rawurlencode($PLATFORMS[$t]["name"])."'><span class='platform os_".$PLATFORMS[$t]["slug"]."'>".$PLATFORMS[$t]["name"]."</span> ".$PLATFORMS[$t]["name"]."</a></li>\n";
      echo "</ul>";

      echo "</td>\n";
      echo "      </tr>\n";
    }

    if ($this->nfos)
    {
      echo "      <tr>\n";
      echo "        <td>nfos :</td>\n";
      echo "        <td>";

      $a = array(); $i = 1;
      foreach($this->nfos as $t)
        $a[] = sprintf("<a href='board_nfo.php?which=%d'>%d</a>",$t->id,$i++);
      echo implode(" ",$a);

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
        echo "<li>".$p->RenderSingleRowShort()."</li>\n";
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

    echo " <div class='foot'>added on the ".$this->board->addedDate;
    if ($this->board->addedUser)
    {
      echo " by ".$this->board->addedUser->PrintLinkedName()." ".$this->board->addedUser->PrintLinkedAvatar();
    }
    echo "</div>\n";

    echo "</div>\n";
  }
};

class PouetBoxBoardList extends PouetBox
{
  public $letter;
  public $letterselect;
  public $boards;
  
  function __construct($letter)
  {
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

  function Load()
  {
    $s = new BM_query("boards");
    $s->AddField("boards.id");
    $s->AddField("boards.sysop");
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

  function RenderBody()
  {
    global $thread_categories;
    echo "<table class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th>name</th>\n";
    echo "  <th>sysop</th>\n";
    echo "  <th>phone number</th>\n";
    echo "</tr>\n";
    foreach ($this->boards as $b) {
      echo "<tr>\n";
      echo "  <td class='boardname'><a href='boards.php?which=".(int)$b->id."'>"._html($b->name)."</a></td>\n";
      echo "  <td>"._html($b->sysop)."</td>\n";
      echo "  <td>"._html($b->phonenumber)."</td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};
///////////////////////////////////////////////////////////////////////////////

$boardID = (int)@$_GET["which"];

$p = null;
if (!$boardID)
{
  $pattern = @$_GET["pattern"] ? @$_GET["pattern"] : chr(rand(ord("a"),ord("z")));
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
