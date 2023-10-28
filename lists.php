<?php
require_once("bootstrap.inc.php");

class PouetBoxListsList extends PouetBox  /* pf lol */
{
  public $letter;
  public $letterselect;
  public $lists;
  function __construct($letter)
  {
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
    $s = new BM_query();
    $s->AddTable("lists");
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

  function RenderBody()
  {
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
  public $id;
  public $list;
  public $prods;
  public $groups;
  public $parties;
  public $users;
  public $maintainers;
  public $canEdit;
  public $canDelete;
  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_listsmain";
    $this->id = (int)$id;

    $this->canEdit = false;
    $this->canDelete = false;
  }

  function LoadFromDB()
  {
    $s = new BM_query();
    $s->AddTable("lists");
    $s->AddField("lists.id");
    $s->AddField("lists.name");
    $s->AddField("lists.desc");
    $s->AddField("lists.addedDate");
    $s->Attach(array("lists"=>"addedUser"),array("users as addedUser"=>"id"));
    $s->Attach(array("lists"=>"owner"),array("users as owner"=>"id"));
    $s->AddWhere(sprintf_esc("lists.id=%d",$this->id));
    list($this->list) = $s->perform();

    if (!$this->list)
    {
      return;
    }

    $s = new BM_query();
    $s->AddTable("list_items");
    $s->Attach(array("list_items"=>"itemid"),array("prods as prod"=>"id"));
    $s->AddWhere(sprintf_esc("list_items.list=%d",$this->id));
    $s->AddWhere("list_items.type='prod'");
    $this->prods = $s->perform();

    $a = array();
    foreach($this->prods as $p) $a[] = &$p->prod;
    PouetCollectPlatforms($a);

    $s = new BM_query();
    $s->AddTable("list_items");
    $s->Attach(array("list_items"=>"itemid"),array("groups as group"=>"id"));
    $s->AddWhere(sprintf_esc("list_items.list=%d",$this->id));
    $s->AddWhere("list_items.type='group'");
    $this->groups = $s->perform();

    $s = new BM_query();
    $s->AddTable("list_items");
    $s->Attach(array("list_items"=>"itemid"),array("parties as party"=>"id"));
    $s->AddWhere(sprintf_esc("list_items.list=%d",$this->id));
    $s->AddWhere("list_items.type='party'");
    $this->parties = $s->perform();

    $s = new BM_query();
    $s->AddTable("list_items");
    $s->Attach(array("list_items"=>"itemid"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("list_items.list=%d",$this->id));
    $s->AddWhere("list_items.type='user'");
    $this->users = $s->perform();

    $s = new BM_query();
    $s->AddTable("list_maintainers");
    $s->Attach(array("list_maintainers"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("list_maintainers.listID = %d",$this->id));
    $this->maintainers = $s->perform();

    global $currentUser;
    if ($currentUser)
    {
      if ($currentUser->id == $this->list->owner->id
        || $currentUser->id == $this->list->addedUser->id
        || $currentUser->IsModerator())
      {
        $this->canEdit = true;
        $this->canDelete = true;
      }
      foreach($this->maintainers as $user)
      {
        if ($currentUser->id == $user->user->id)
        {
          $this->canEdit = true;
        }
      }
    }
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
        if ($this->CanEdit())
        {
          printf("  <span class='list-delete'><input type='submit' name='listDeleteGroup[%d]' value='delete'/></span>",$d->group->id);
        }
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
        echo $d->prod->RenderSingleRowShort();
        echo "</span>\n";
        echo "<span>\n";
        if ($d->prod->placings)
          echo $d->prod->placings[0]->PrintResult();
        echo "</span>\n";
        echo "<span>\n";
        echo $d->prod->RenderReleaseDate();
        echo "</span>\n";
        if ($this->CanEdit())
        {
          printf("  <span class='list-delete'><input type='submit' name='listDeleteProd[%d]' value='delete'/></span>",$d->prod->id);
        }
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
        if ($this->CanEdit())
        {
          printf("  <span class='list-delete'><input type='submit' name='listDeleteParty[%d]' value='delete'/></span>",$d->party->id);
        }
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
        if ($this->CanEdit())
        {
          printf("  <span class='list-delete'><input type='submit' name='listDeleteUser[%d]' value='delete'/></span>",$d->user->id);
        }
        echo "</li>\n";
      }
      echo "</ul>\n";
    }
    echo " <div class='foot'>added on the ".$this->list->addedDate." by ".$this->list->addedUser->PrintLinkedName()." ".$this->list->addedUser->PrintLinkedAvatar()."</div>\n";
    echo "</div>\n";
  }
  function CanEdit()
  {
    return $this->canEdit;
  }
  function CanDelete()
  {
    return $this->canDelete;
  }
  use PouetForm;
  function Validate($post)
  {
    return $this->CanEdit() ? array() : array("lol no !");
  }
  function Commit($post)
  {
    if ($post["listDeleteParty"])
    {
      $ids = array_map(function($i){ return (int)$i; },array_keys($post["listDeleteParty"]));
      SQLLib::Query(sprintf_esc("DELETE FROM list_items WHERE list=%d AND type='party' AND itemid IN (".implode(",",$ids).")",$this->list->id));
    }
    if ($post["listDeleteProd"])
    {
      $ids = array_map(function($i){ return (int)$i; },array_keys($post["listDeleteProd"]));
      SQLLib::Query(sprintf_esc("DELETE FROM list_items WHERE list=%d AND type='prod' AND itemid IN (".implode(",",$ids).")",$this->list->id));
    }
    if ($post["listDeleteGroup"])
    {
      $ids = array_map(function($i){ return (int)$i; },array_keys($post["listDeleteGroup"]));
      SQLLib::Query(sprintf_esc("DELETE FROM list_items WHERE list=%d AND type='group' AND itemid IN (".implode(",",$ids).")",$this->list->id));
    }
    if ($post["listDeleteUser"])
    {
      $ids = array_map(function($i){ return (int)$i; },array_keys($post["listDeleteUser"]));
      SQLLib::Query(sprintf_esc("DELETE FROM list_items WHERE list=%d AND type='user' AND itemid IN (".implode(",",$ids).")",$this->list->id));
    }
    return array();
  }
};
///////////////////////////////////////////////////////////////////////////////

class PouetBoxListsAdd extends PouetBox
{
  public $box;
  public $list;
  public $formifier;
  public $fields;
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
        try
        {
          SQLLib::InsertRow("list_items",$a);
        }
        catch(SQLLibException $e)
        {
          if ($e->getCode() == 1062)
          {
            return array("that's already added! :o");
          }
          else throw $e;
        }
        $added = true;
      }
    }
    return $added ? array() : array("you didn't add anything ! :(");
  }

  function RenderContent()
  {
    $this->formifier->RenderForm( $this->fields );
?>
<script>
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
  new Autocompleter($("groupID"), {"dataUrl":"./ajax_groups.php","processRow": function(item) {
    return item.name.escapeHTML() + (item.disambiguation ? " <span class='group-disambig'>" + item.disambiguation.escapeHTML() + "</span>" : "");
  }});
  new Autocompleter($("userID"),  {"dataUrl":"./ajax_users.php","processRow": function(item) {
    return "<img class='avatar' src='<?=POUET_CONTENT_URL?>avatars/" + item.avatar.escapeHTML() + "'/> " + item.name.escapeHTML() + " <span class='glops'>" + item.glops + " glöps</span>";
  }});
});
//-->
</script>
<?php
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
  public $box;
  public $list;
  public $formifier;
  public $fields;
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

    if (!$post["maintainerID"])
      return array("something is missing ?!");

    return array();
  }

  function Commit($post)
  {
    $a = array();
    $a["listID"] = $this->list->id;
    $a["userID"] = (int)$post["maintainerID"];
    SQLLib::InsertRow("list_maintainers",$a);
    return array();
  }

  function RenderContent()
  {
    $this->formifier->RenderForm( $this->fields );
?>
<script>
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
<?php
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

class PouetBoxListsDelete extends PouetBox
{
  public $list;
  public $checkString;
  function __construct( $list )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_listsdelete";

    $this->classes[] = "errorbox";

    $this->list = $list;

    global $verificationStrings;
    $this->checkString = $verificationStrings[ array_rand($verificationStrings) ];

    $this->title = "delete this list: "._html( $this->list->name );
  }
  use PouetForm;
  function Validate($data)
  {
    if ($data["check"] != $data["checkOrig"])
      return array("wrong verification string !");
    return array();
  }
  function Commit($data)
  {
    SQLLib::Query(sprintf_esc("DELETE FROM list_items WHERE list=%d",$this->list->id));
    SQLLib::Query(sprintf_esc("DELETE FROM list_maintainers WHERE listID=%d",$this->list->id));
    SQLLib::Query(sprintf_esc("DELETE FROM lists WHERE id=%d",$this->list->id));
    return array();
  }
  function RenderBody()
  {
    echo "<div class='content'/>";
    echo "  <p>To make sure you want to delete <b>this</b> list, type \"".$this->checkString."\" here:</p>";
    echo "  <input name='checkOrig' type='hidden' value='"._html($this->checkString)."'/>";
    echo "  <input id='check' name='check' autocomplete='no'/>";
    echo "</div>";
    echo "<div class='foot'/>";
    echo "  <input type='submit' value='Submit' />";
    echo "</div>";
    ?>
<script>
document.observe("dom:loaded",function(){
  $("pouetbox_listsdelete").up("form").observe("submit",function(e){
    if ($F("check") != "<?=_js($this->checkString)?>")
    {
      alert("Enter the verification string!");
      e.stop();
      return;
    }
    if (!confirm("ARE YOU REALLY SURE YOU WANT TO DELETE \"<?=_js($this->list->name)?>\"?!"))
      e.stop();
  });
});
</script>
    <?php
  }
}

$listID = (int)@$_GET["which"];

$form = null;
$p = null;
if (!$listID)
{
  $pattern = @$_GET["pattern"] ? @$_GET["pattern"] : chr(rand(ord("a"),ord("z")));
  $p = new PouetBoxListsList($pattern);
  $p->Load();
  $TITLE = "lists: ".$p->letter;
}
else
{
  $form = new PouetFormProcessor();
  $main = new PouetBoxListsMain($listID);
  $form->Add( "listmain", $main );
  $main->Load();
  if ($main->list)
  {
    $TITLE = $main->list->name;

    if ($main->CanEdit())
    {
      $form->SetSuccessURL( "lists.php?which=".(int)$listID, true );
      $form->Add( "list_add", new PouetBoxListsAdd($main) );
      if ($main->CanDelete())
      {
        $form->Add( "list_addmaintainer", new PouetBoxListsAddMaintainer($main) );
        $form->Add( "list_delete", new PouetBoxListsDelete($main->list) );
      }

      $form->Process();
    }
  }
  else
  {
    $form = null;
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
