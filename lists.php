<?
require_once("bootstrap.inc.php");

class PouetBoxListsList extends PouetBox  /* pf lol */
{
  var $letter;
  function __construct($letter) {
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
    $s->Attach(array("lists"=>"owner"),array("users as owner"=>"id"));
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
    echo "  <th>owner</th>\n";
    echo "</tr>\n";
    foreach ($this->lists as $l) {
      echo "<tr>\n";
      echo "  <td class='listname'><a href='lists.php?which=".(int)$l->id."'>"._html($l->name)."</a></td>\n";
      echo "  <td>"._html(shortify($l->desc))."</td>\n";
      echo "  <td>".$l->owner->PrintLinkedAvatar()." ".$l->owner->PrintLinkedName()."</td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};

///////////////////////////////////////////////////////////

class PouetBoxListsMain extends PouetBox
{
  function __construct($id) {
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
    $s->AddField("lists.addedDate");
    $s->Attach(array("lists"=>"addedUser"),array("users as addedUser"=>"id"));
    $s->Attach(array("lists"=>"owner"),array("users as owner"=>"id"));
    $s->AddWhere(sprintf_esc("lists.id=%d",$this->id));
    list($this->list) = $s->perform();

    $s = new BM_query("list_items");
    $s->Attach(array("list_items"=>"itemid"),array("prods as prod"=>"id"));
    $s->AddWhere(sprintf_esc("list_items.list=%d",$this->id));
    $s->AddWhere("list_items.type='prod'");
    $this->prods = $s->perform();

    $a = array();
    foreach($this->prods as $p) $a[] = &$p->prod;
    PouetCollectPlatforms($a);

    $s = new BM_query("list_items");
    $s->Attach(array("list_items"=>"itemid"),array("groups as group"=>"id"));
    $s->AddWhere(sprintf_esc("list_items.list=%d",$this->id));
    $s->AddWhere("list_items.type='group'");
    $this->groups = $s->perform();

    $s = new BM_query("list_items");
    $s->Attach(array("list_items"=>"itemid"),array("parties as party"=>"id"));
    $s->AddWhere(sprintf_esc("list_items.list=%d",$this->id));
    $s->AddWhere("list_items.type='party'");
    $this->parties = $s->perform();

    $s = new BM_query("list_items");
    $s->Attach(array("list_items"=>"itemid"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("list_items.list=%d",$this->id));
    $s->AddWhere("list_items.type='user'");
    $this->users = $s->perform();

    $s = new BM_query("list_maintainers");
    $s->Attach(array("list_maintainers"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("list_maintainers.listID = %d",$this->id));
    $this->maintainers = $s->perform();

  }

  function Render()
  {
    global $currentUser;
    echo "<div id='".$this->uniqueID."' class='pouettbl'>\n";
    echo "<div id='listsname'>\n";
    echo $this->list->name;
    echo "</div>\n";

    echo " <div class='content' id='description'>".nl2br(_html($this->list->desc))."</div>\n";

    echo "<h2>maintainers</h2>";
    echo "<ul class='boxlist'>\n";
    echo " <li>".$this->list->owner->PrintLinkedAvatar()." ".$this->list->owner->PrintLinkedName()." <b>(owner)</b></li>\n";
    foreach($this->maintainers as $user)
    {
      echo " <li>".$user->user->PrintLinkedAvatar()." ".$user->user->PrintLinkedName()."</li>\n";
    }
    echo "</ul>\n";

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
    echo " <div class='foot'>added on the ".$this->list->addedDate." by ".$this->list->addedUser->PrintLinkedName()." ".$this->list->addedUser->PrintLinkedAvatar()."</div>\n";
    echo "</div>\n";
  }
  function CanEdit()
  {
    global $currentUser;    
    if (!$currentUser) return false;
    
    if ($currentUser->id == $this->list->owner->id 
      || $currentUser->id == $this->list->addedUser->id
      || $currentUser->IsModerator())
      return true;
    
    foreach($this->maintainers as $user)
    {
      if ($user->user->id == $currentUser->id)
      {
        return true;
      }
    }
    return false;
  }
  function CanDelete()
  {
    global $currentUser;    
    if (!$currentUser) return false;
    
    if ($currentUser->id == $this->list->owner->id 
      || $currentUser->id == $this->list->addedUser->id
      || $currentUser->IsModerator())
      return true;
    
    return false;
  }
};
///////////////////////////////////////////////////////////////////////////////

class PouetBoxListsAdd extends PouetBox
{
  function __construct($box)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_listsadd";
    $this->box = $box;
    $this->list = $box->list;
    $this->formifier = new Formifier();
    $this->fields = array(
      "prodID"=>array(
        "name"=>"add prod",
      ),
      "groupID"=>array(
        "name"=>"add group",
      ),
      "partyID"=>array(
        "name"=>"add party",
      ),
      "userID"=>array(
        "name"=>"add user",
      ),
    );
    $this->title = "add item to list";
  }
  use PouetForm;
  function Validate($post)
  {
    global $currentUser;

    if (!$currentUser)
      return array("you have to be logged in!");
      
    if (!$this->box->CanEdit())
      return array("not allowed lol !");
    
    return array();
  }

  function Commit($post)
  {
    $items = array("prod","group","party","user");
    $added = false;
    foreach($items as $v)
    {
      if ($post[$v."ID"])
      {
        $a = array();
        $a["list"] = $this->list->id;
        $a["type"] = $v;
        $a["itemid"] = $post[$v."ID"];
        SQLLib::InsertRow("list_items",$a);
        $added = true;
      }
    }
    return $added ? array() : array("you didn't add anything ! :(");
  }
  
  function RenderContent()
  {
    $this->formifier->RenderForm( $this->fields );
?>
<script type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  new Autocompleter($("prodID"), {"dataUrl":"./ajax_prods.php",
    "width":320,
    "processRow": function(item) {
      var s = item.name.escapeHTML();
      if (item.groupName) s += " <small class='group'>" + item.groupName.escapeHTML() + "</small>";
      return s;
    }
  });
  new Autocompleter($("partyID"), {"dataUrl":"./ajax_parties.php"});
  new Autocompleter($("groupID"), {"dataUrl":"./ajax_groups.php"});
  new Autocompleter($("userID"),  {"dataUrl":"./ajax_users.php",
    "processRow": function(item) {
      return "<img class='avatar' src='<?=POUET_CONTENT_URL?>avatars/" + item.avatar.escapeHTML() + "'/> " + item.name.escapeHTML() + " <span class='glops'>" + item.glops + " glöps</span>";
    }
  });
});
//-->
</script>
<?
  }
  function RenderFooter()
  {
    echo "<div class='foot'>\n";
    echo " <input type='submit' value='Submit' id='submit'>";
    echo "</div>\n";
    echo "</div>";
  }
}

///////////////////////////////////////////////////////////////////////////////

class PouetBoxListsAddMaintainer extends PouetBox
{
  function __construct($box)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_listsaddmaintainer";
    $this->box = $box;
    $this->list = $box->list;
    $this->formifier = new Formifier();
    $this->fields = array(
      "maintainerID"=>array(
        "name"=>"add maintainer",
      ),
    );
    $this->title = "add maintainer to list";
  }
  use PouetForm;
  function Validate($post)
  {
    global $currentUser;

    if (!$currentUser)
      return array("you have to be logged in!");
      
    if (!$this->box->CanDelete())
      return array("not allowed lol !");
    
    return array();
  }

  function Commit($post)
  {
    $a = array();
    $a["listID"] = $this->list->id;
    $a["userID"] = $post["maintainerID"];
    SQLLib::InsertRow("list_maintainers",$a);
    return array();
  }
  
  function RenderContent()
  {
    $this->formifier->RenderForm( $this->fields );
?>
<script type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  new Autocompleter($("maintainerID"),  {"dataUrl":"./ajax_users.php",
    "processRow": function(item) {
      return "<img class='avatar' src='<?=POUET_CONTENT_URL?>avatars/" + item.avatar.escapeHTML() + "'/> " + item.name.escapeHTML() + " <span class='glops'>" + item.glops + " glöps</span>";
    }
  });
});
//-->
</script>
<?
  }
  function RenderFooter()
  {
    echo "<div class='foot'>\n";
    echo " <input type='submit' value='Submit' id='submit'>";
    echo "</div>\n";
    echo "</div>";
  }
}
$listID = (int)$_GET["which"];

$form = null;
$p = null;
if (!$listID)
{
  $pattern = $_GET["pattern"] ? $_GET["pattern"] : chr(rand(ord("a"),ord("z")));
  $p = new PouetBoxListsList($pattern);
  $p->Load();
  $TITLE = "lists: ".$p->letter;
}
else
{
  $p = new PouetBoxListsMain($listID);
  $p->Load();
  if ($p->list)
  {
    $TITLE = $p->list->name;
    
    if ($p->CanEdit())
    {
      $form = new PouetFormProcessor();
      $form->SetSuccessURL( "lists.php?which=".(int)$listID, true );
      $form->Add( "list_add", new PouetBoxListsAdd($p) );
      if ($p->CanDelete())
      {
        $form->Add( "list_addmaintainer", new PouetBoxListsAddMaintainer($p) );
      }
  
      $form->Process();
    }
  }
  else
  {
    $p = null;
  }
}

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
if($p) $p->Render();
if($form) $form->Display();
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
