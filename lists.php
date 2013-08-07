<?
include_once("bootstrap.inc.php");

class PouetBoxListsMain extends PouetBox 
{
  function PouetBoxListsMain($id) {
    parent::__construct();
    $this->uniqueID = "pouetbox_listsmain";
    $this->id = (int)$id;
    
  }
  
  function LoadFromDB() 
  {
    $s = new BM_query("lists");
    $s->AddField("lists.id");
    $s->AddField("lists.name");
    $s->AddField("lists.desc");
    $s->AddField("lists.added");
    $s->Attach(array("lists"=>"adder"),array("users as adder"=>"id"));
    $s->Attach(array("lists"=>"upkeeper"),array("users as upkeeper"=>"id"));
    $s->AddWhere(sprintf_esc("lists.id=%d",$this->id));
    list($this->list) = $s->perform();
    
    $s = new BM_query("listitems");
    $s->Attach(array("listitems"=>"itemid"),array("prods as prod"=>"id"));
    $s->AddWhere(sprintf_esc("listitems.list=%d",$this->id));
    $s->AddWhere("listitems.type='prod'");
    $this->prods = $s->perform();
    
    $a = array();
    foreach($this->prods as $p) $a[] = &$p->prod;
    PouetCollectPlatforms($a);
    
    $s = new BM_query("listitems");
    $s->Attach(array("listitems"=>"itemid"),array("groups as group"=>"id"));
    $s->AddWhere(sprintf_esc("listitems.list=%d",$this->id));
    $s->AddWhere("listitems.type='group'");
    $this->groups = $s->perform();
        
    $s = new BM_query("listitems");
    $s->Attach(array("listitems"=>"itemid"),array("parties as party"=>"id"));
    $s->AddWhere(sprintf_esc("listitems.list=%d",$this->id));
    $s->AddWhere("listitems.type='party'");
    $this->parties = $s->perform();
      
    $s = new BM_query("listitems");
    $s->Attach(array("listitems"=>"itemid"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("listitems.list=%d",$this->id));
    $s->AddWhere("listitems.type='user'");
    $this->users = $s->perform();
                
    
  }

  function Render() 
  {
    global $currentUser,$PLATFORMS;
    echo "<div id='".$this->uniqueID."' class='pouettbl'>\n";
    echo "<div id='listsname'>\n";
    echo sprintf("<a href='lists.php?which=%d'>%s</a>",$this->id,_html($this->list->name));

    if ($currentUser && $currentUser->CanEditItems())
    {
      printf("<div id='adminlinks'>");
      //printf("[<a href='admin_board_edit.php?which=%d' class='adminlink'>edit</a>]\n",$this->id);
      printf("</div>");
    }
    echo "</div>\n";
    
    echo " <div class='content'>upkept by ".$this->list->upkeeper->PrintLinkedName()." ".$this->list->upkeeper->PrintLinkedAvatar()."</div>\n";
    
    if ($this->groups)
    {
      echo "<h2>groups</h2>";
      echo "<ul class='boxlist boxlisttable'>\n";
      foreach($this->groups as $d) 
      {
        echo "<li>\n";
        echo "<span>\n";
        echo $d->group->RenderFull();
        echo "</span>\n";
        echo "</li>\n";
      }
      echo "</ul>\n";
    }
    
    if ($this->prods)
    {
      echo "<h2>prods</h2>";
      echo "<ul class='boxlist boxlisttable'>\n";
      foreach($this->prods as $d) 
      {
        echo "<li>\n";
        echo "<span>\n";
        echo $d->prod->RenderTypeIcons();
        echo $d->prod->RenderPlatformIcons();
        echo "<span class='prod'>".$d->prod->RenderLink()."</span>\n";
        echo "</span>\n";
        echo "<span>\n";
        if ($d->prod->placings)
          echo $d->prod->placings[0]->PrintResult($p->year);
        echo "</span>\n";
        echo "<span>\n";
        echo $d->prod->RenderReleaseDate();
        echo "</span>\n";
        echo "</li>\n";
      }
      echo "</ul>\n";
    }
    
    if ($this->parties)
    {
      echo "<h2>parties</h2>";
      echo "<ul class='boxlist boxlisttable'>\n";
      foreach($this->parties as $d) 
      {
        echo "<li>\n";
        echo "<span>\n";
        echo $d->party->RenderFull();
        echo "</span>\n";
        echo "</li>\n";
      }
      echo "</ul>\n";
    }
    
    if ($this->users)
    {
      echo "<h2>users</h2>";
      echo "<ul class='boxlist boxlisttable'>\n";
      foreach($this->users as $d) 
      {
        echo "<li>\n";
        echo "<span>\n";
        echo $d->user->PrintLinkedAvatar()." ";
        echo $d->user->PrintLinkedName();
        echo "</span>\n";
        echo "<span>\n";
        echo $d->user->glops." glöps";
        echo "</span>\n";
        echo "</li>\n";
      }
      echo "</ul>\n";
    }
    echo " <div class='foot'>added on the ".$this->list->added." by ".$this->list->adder->PrintLinkedName()." ".$this->list->adder->PrintLinkedAvatar()."</div>\n";
    echo "</div>\n";
  }
};

class PouetBoxListsList extends PouetBox  /* pf lol */
{
  var $letter;
  function PouetBoxListsList($letter) {
    parent::__construct();
    $this->uniqueID = "pouetbox_listslist";
    
    $letter = substr($letter,0,1);
    if (preg_match("/^[a-z]$/",$letter))
      $this->letter = $letter;
    else
      $this->letter = "#";
    
    $a = array();
    $a[] = "<a href='lists.php?pattern=%23'>#</a>";
    for($x=ord("a");$x<=ord("z");$x++)
      $a[] = sprintf("<a href='lists.php?pattern=%s'>%s</a>",chr($x),chr($x));

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
    $s = new BM_query("lists");
    $s->AddField("lists.id");
    $s->AddField("lists.name");
    $s->AddField("lists.desc");
    $s->Attach(array("lists"=>"upkeeper"),array("users as upkeeper"=>"id"));
    if ($this->letter=="#")
      $s->AddWhere(sprintf("name regexp '^[^a-z]'"));
    else
      $s->AddWhere(sprintf("name like '%s%%'",$this->letter));
    $s->AddOrder("name");
    $this->lists = $s->perform();
  }

  function RenderBody() {
    global $thread_categories;
    echo "<table class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th>name</th>\n";
    echo "  <th>description</th>\n";
    echo "  <th>upkeeper</th>\n";
    echo "</tr>\n";
    foreach ($this->lists as $l) {
      echo "<tr>\n";
      echo "  <td class='boardname'><a href='lists.php?which=".(int)$l->id."'>"._html($l->name)."</a></td>\n";
      echo "  <td>"._html($l->desc)."</td>\n";
      echo "  <td>".$l->upkeeper->PrintLinkedAvatar()." ".$l->upkeeper->PrintLinkedName()."</td>\n";
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
  $p = new PouetBoxListsList($pattern);
  $p->Load();
  $TITLE = "lists: ".$p->letter;
} 
else
{
  $p = new PouetBoxListsMain($boardID);
  $p->Load();
  $TITLE = $p->list->name;
}

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
if($p) $p->Render();
echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>
