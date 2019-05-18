<?
require_once("bootstrap.inc.php");
require_once("include_generic/countries.inc.php");
require_once("include_pouet/box-modalmessage.php");

$COUNTRIES = array_merge(array(""),$COUNTRIES);

$avatars = glob(POUET_CONTENT_LOCAL."avatars/*.gif");

$success = null;

$namesNumeric = array(
  // numbers
  "indextopglops" => "front page - top glops",
  "indextopprods" => "front page - top prods (recent)",
  "indextopkeops" => "front page - top prods (all-time)",
  "indexoneliner" => "front page - oneliner",
  "indexlatestadded" => "front page - latest added",
  "indexlatestreleased" => "front page - latest released",
  "indexojnews" => "front page - bitfellas news",
  "indexlatestcomments" => "front page - latest comments",
  "indexlatestparties" => "front page - latest parties",
  "indexbbstopics" => "front page - bbs topics",
  "indexwatchlist" => "front page - watchlist",
  "bbsbbstopics" => "bbs page - bbs topics",
  "prodlistprods" => "prodlist page - prods",
  "userlistusers" => "userlist page - users",
  "searchprods" => "search page - prods",
  "userlogos" => "user page - logos",
  "userprods" => "user page - prods",
  "usergroups" => "user page - groups",
  "userparties" => "user page - parties",
  "userscreenshots" => "user page - screenshots",
  "usernfos" => "user page - nfos",
  "usercomments" => "user page - comments",
  "userrulez" => "user page - rulez",
  "usersucks" => "user page - sucks",
  "commentshours" => "comments page - hours",
  "topicposts" => "topic page - posts",
);
$namesSwitch = array(
  //select
  "logos" => "logos",
  "topbar" => "top bar",
  "bottombar" => "bottom bar",
  "indexcdc" => "front page - cdc",
  "indexsearch" => "front page - search",
  "indexstats" => "front page - stats",
  "indexlinks" => "front page - links",
  "indexplatform" => "front page - show platform icons",
  "indextype" => "front page - show type icons",
  "indexwhoaddedprods" => "front page - who added prods",
  "indexwhocommentedprods" => "front page - who commented prods",
  "topichidefakeuser" => "bbs page - hide fakeuser",
  "prodhidefakeuser" => "prod page - hide fakeuser",
  "displayimages" => "[img][/img] tags should be displayed as...",
  "indexbbsnoresidue" => "residue threads on the front page are...",
);

class PouetBoxAccount extends PouetBox
{
  function __construct( )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_account";
    $this->title = "e-e-e-edit your account";
    $this->formifier = new Formifier();
    $this->maxCDCs = 20;
  }

  function LoadFromDB()
  {
    global $COUNTRIES;
    $this->cdcs = array();

    $query = new BM_Query( "users" );
    $query->AddExtendedFields();
//      foreach(PouetUser::getExtendedFields() as $v)
//        $query->AddField("users.".$v);
    $query->AddWhere(sprintf_esc("users.id = %d",get_login_id()));
    $query->SetLimit(1);

    $s = new BM_Query("users_im");
    $s->AddWhere(sprintf_esc("users_im.userID = %d",get_login_id()));
    $this->ims = $s->perform();

    $s = $query->perform();
    $this->user = reset( $s );

    $rows = SQLLib::SelectRows(sprintf_esc("select cdc from users_cdcs where user=%d",get_login_id()));
    foreach($rows as $r)
      $this->cdcs[] = $r->cdc;
      
    $this->fieldsPouet = array(
      "nickname"=>array(
        "info"=>"how do you look on IRC ?",
        "required"=>true,
        "value"=>$this->user->nickname,
        "html"=>"maxlength='16'",
      ),
      "avatar"=>array(
        "info"=>"your faaaaaaace is like a song",
        "required"=>true,
        "value"=>$this->user->avatar,
        "type"=>"avatar",
        "infoAfter"=>"(<a href='submit_avatar.php'>upload new</a>) <span id='randomAvatar'></span> <span id='avatarPicker'></span>",
      ),
      "slengpung"=>array(
        "info"=>"your slengpung id, if you have one",
        "value"=>$this->user->slengpung,
        "type"=>"number",
      ),
      "csdb"=>array(
        "info"=>"your csdb id, if you have one",
        "value"=>$this->user->csdb,
        "type"=>"number",
      ),
      "zxdemo"=>array(
        "info"=>"your zxdemo id, if you have one",
        "value"=>$this->user->zxdemo,
        "type"=>"number",
      ),
      "demozoo"=>array(
        "info"=>"your demozoo id, if you have one",
        "value"=>$this->user->demozoo,
        "type"=>"number",
      ),
    );

    $this->fieldsCDC = array();
    $glop = POUET_CDC_MINGLOP;
    for ($x=1; $x < $this->maxCDCs; $x++)
    {
      /*
      $cdcText = array(
        "your favorite",
        "you love this when you're drunk",
        "the one on that weird platform",
        "",
        "",
        "",
        "",
        "",
        "",
        "",
      );
      */

      if ($this->user->glops >= $glop)
      {
        $this->fieldsCDC["cdc".$x] = array(
          "value" => $this->cdcs[$x-1],
          "name" => "coup de coeur ".$x." (".$glop." glöps)",
          "info" => $cdcText[$x], // is this cool?
        );
      }
      $glop *= 2;
    }


    $row = SQLLib::SelectRow("DESC users_im im_type");
    $this->imTypes = enum2array($row->Type);

    $this->ims[] = new stdClass();
    $this->fieldsIM = array();
    $n = 0;
    foreach($this->ims as $im)
    {
      $this->fieldsIM["im_type".$n] = array(
        //"info"=>"the one you really use",
        "name"=>"contact type",
        "type"=>"select",
        "value"=>$im->im_type,
        "fields"=>$this->imTypes,
      );
      $this->fieldsIM["im_id".$n] = array(
        //"info"=>"buuuuuuuuuuuuuuuu .... hiho !",
        "name"=>"contact address",
        "value"=>$im->im_id,
      );
      $n++;
    }

    global $namesNumeric;
    global $namesSwitch;

    if ($_POST)
    {
      foreach($_POST as $k=>$v)
      {
        if ($this->fieldsPouet[$k]) $this->fieldsPouet[$k]["value"] = $v;
        if ($this->fieldsCDC[$k]) $this->fieldsCDC[$k]["value"] = $v;
      }
    }
  }

  function ParsePostLoggedIn( $data )
  {
    global $currentUser;

    $errors = array();

    // cdc bit

    $cdcUnique = array();
    $glop = POUET_CDC_MINGLOP;
    for ($x=1; $x < $this->maxCDCs; $x++)
    {
      if ($this->user->glops >= $glop && $data["cdc".$x])
      {
        $cdcUnique[] = $data["cdc".$x];
      }
      $glop *= 2;
    }
    $cdcUnique = array_unique($cdcUnique);
    SQLLib::Query(sprintf_esc("delete from users_cdcs where user = %d",get_login_id()));
    foreach($cdcUnique as $c)
    {
      $a = array();
      $a["user"] = get_login_id();
      $a["cdc"] = $c;
      SQLLib::InsertRow("users_cdcs",$a);
    }

    // im bit

    SQLLib::Query(sprintf_esc("delete from users_im where userID = %d",get_login_id()));
    for ($n = 0; $n < count($this->imTypes); $n++)
    {
      $a = array();
      $a["userID"] = get_login_id();
      $a["im_type"] = $data["im_type".$n];
      $a["im_id"] = $data["im_id".$n];
      if ($a["im_type"] && $a["im_id"])
      {
        SQLLib::InsertRow("users_im",$a);
      }
    }

    // pouet bit

    global $avatars;

    $sql = array();
    foreach ($this->fieldsPouet as $k=>$v)
    {
      if ($k == "nickname" && !trim($data[$k])) continue;
      //if (trim($data[$k]))
      $sql[$k] = trim($data[$k]);
    }

    if (!$sql["avatar"] || !file_exists(POUET_CONTENT_LOCAL . "avatars/".$sql["avatar"]))
      $sql["avatar"] = basename( $avatars[ array_rand($avatars) ] );

    SQLLib::UpdateRow("users",$sql,"id=".(int)get_login_id());

    if ($currentUser->nickname != $data["nickname"])
    {
      $a = array();
      $a["user"] = $currentUser->id;
      $a["nick"] = $currentUser->nickname;
      SQLLib::InsertRow("oldnicks",$a);
    }

    // customizer bit

    global $avatars;

    global $success;
    if (!$errors) $success = "modifications complete!";

    return $errors;
  }
  use PouetForm;
  function ParsePostMessage( $data )
  {
    if (!get_login_id())
      return array("You have to be logged in!");
      
    $errors = array();

    $data["nickname"] = strip_tags($data["nickname"]);
    $data["nickname"] = trim($data["nickname"]);

    if (strlen($data["nickname"]) < 2)
    {
      $errors[] = "nick too short!";
      return $errors;
    }

    if (!$errors)
    {
      $this->LoadFromDB();
      
      $errors = $this->ParsePostLoggedIn( $data );
    }
//    $this->LoadFromDB();
    return $errors;
  }

  function Render()
  {
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    echo "  <h2>".$this->title."</h2>\n";
    echo "  <div class='accountsection content'>\n";
    echo "    <p><b>reminder:</b> pou&euml;t handles none of your personal information;";
    echo "    if you want to change your password, you can do it <a href='https://id.scene.org/profile/'>here</a>.</p>\n";
    echo "  </div>\n";
    
    echo "  <h2>pou&euml;t things</h2>\n";
    echo "  <div class='accountsection content'>\n";
    $this->formifier->RenderForm( $this->fieldsPouet );
    echo "  </div>\n";
    if ($this->fieldsCDC)
    {
      echo "  <h2>coup de coeurs</h2>\n";
      echo "  <div class='accountsection content account-cdcs'>\n";
      $this->formifier->RenderForm( $this->fieldsCDC );
      echo "  </div>\n";
    }
    if ($this->fieldsIM)
    {
      echo "  <h2>contact details</h2>\n";
      echo "  <div class='accountsection content account-ims'>\n";
      $this->formifier->RenderForm( $this->fieldsIM );
      echo "  </div>\n";
    }
    echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};

class PouetBoxAccountModificationRequests extends PouetBox
{
  function __construct( )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_accountreq";
    $this->title = "your most recent modification requests";
  }
  use PouetForm;
  function LoadFromDB()
  {
    global $currentUser;
    
    $s = new BM_Query("modification_requests");
    $s->AddField("modification_requests.id");
    $s->AddField("modification_requests.requestType");
    $s->AddField("modification_requests.itemID");
    $s->AddField("modification_requests.itemType");
    $s->AddField("modification_requests.requestBlob");
    $s->AddField("modification_requests.requestDate");
    $s->AddField("modification_requests.approved");
    $s->AddField("modification_requests.comment");
    //$s->Attach(array("modification_requests"=>"gloperatorID"),array("users as gloperator"=>"id"));
    $s->Attach(array("modification_requests"=>"itemID"),array("prods as prod"=>"id"));
    $s->AddWhere(sprintf_esc("userID = %d",$currentUser->id));
    $s->AddOrder("requestDate desc");
    $s->SetLimit( $_GET["limit"] ?: 10 );
    $this->requests = $s->perform();
  }
  function Render()
  {
    global $REQUESTTYPES;
    echo "<table id='".$this->uniqueID."' class='boxtable'>\n";
    echo "  <tr>\n";
    echo "    <th colspan='4'>".$this->title."</th>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <th>date</th>\n";
    echo "    <th>item</th>\n";
    echo "    <th>request</th>\n";
    echo "    <th>approved?</th>\n";
    echo "  </tr>\n";
    foreach($this->requests as $r)
    {
      echo "  <tr>\n";
      echo "    <td>".$r->requestDate."</td>\n";
      echo "    <td>".$r->itemType.": ";
      switch ($r->itemType)
      {
        case "prod": if ($r->prod) echo $r->prod->RenderSingleRowShort();
      }
      echo "</td>\n";
      if ($REQUESTTYPES[$r->requestType])
        echo "    <td>".$REQUESTTYPES[$r->requestType]::Describe()."</td>\n";
      else
        echo "    <td>unknown request type</td>";
      echo "    <td>";
      if ( $r->approved === NULL ) echo "<b>pending</b>";
      else if ( $r->approved == 0 ) echo "<b>no</b> :: "._html($r->comment);
      else if ( $r->approved == 1 ) echo "<b>yes</b>";
      echo "</td>\n";
      echo "  </tr>\n";
    }
    echo "</table>\n";
  }
}

///////////////////////////////////////////////////////////////////////////////

if (!get_login_id())
{
  require_once("include_pouet/header.php");
  require("include_pouet/menu.inc.php");

  $message = new PouetBoxModalMessage(false,true);
  $message->classes[] = "errorbox";
  $message->title = "An error has occured:";
  $message->message = "You need to be logged in for this!";
  $message->Render();
}
else
{

  $form = new PouetFormProcessor();
  
  //if (!get_login_id())
  //  $form->successMessage = "registration complete! a confirmation mail will be sent to your address soon - you can't login until you confirmed your email address!";
    
  $form->SetSuccessURL( "index.php", false );
  
  $account = new PouetBoxAccount();
  $form->Add( "account", $account );
  $form->Add( "accountReq", new PouetBoxAccountModificationRequests() );
  
  $form->Process();
  
  $TITLE = "account!";
  
  require_once("include_pouet/header.php");
  require("include_pouet/menu.inc.php");
  
  echo "<div id='content'>\n";
  
  $form->Display();
  
  echo "</div>\n";

?>
<script>
<!--
document.observe("dom:loaded",function(){
  if (!$("avatarlist"))
    return;

  var updateAvatar = function()
  {
    $("avatarimg").src = "<?=POUET_CONTENT_URL?>avatars/" + $("avatar").options[ $("avatar").selectedIndex ].value;
  }

  var img = new Element("img",{"id":"avatarimg","width":16,"height":16});
  $("avatarlist").insertBefore(img,$("avatar"));
  updateAvatar();
  $("avatarlist").observe("change",updateAvatar);
  $("avatarlist").observe("keyup",updateAvatar);

  $("randomAvatar").update("(<a href='#'>pick random</a>)")
  $("randomAvatar").down("a").observe("click",function(ev){
    ev.stop();
    $("avatar").selectedIndex = Math.floor( Math.random() * $("avatar").options.length );
    updateAvatar();
  });
  
  var div = new Element("div",{"id":"avatarPickerPalette"})
  $("avatarPicker").parentNode.insert(div);
  
  div.insert( new Element("a",{"href":"#"}).observe("click",function(e){
    regeneratePalette();
  }).update("show more") );
  div.insert( new Element("a",{"href":"#","id":"avatarPickerClose"}).observe("click",function(e){
    $("avatarPickerPalette").hide();
  }).update("close") );
  div.insert( new Element("div",{"id":"paletteAvatars"}) );
  var regeneratePalette = function()
  {
    $("paletteAvatars").update();
    for (var i=0; i<100; i++)
    {
      var src = $("avatar").options[ Math.floor( Math.random() * $("avatar").options.length ) ].value;
      var img = new Element("img",{"src":"<?=POUET_CONTENT_URL?>avatars/" + src,"data-src":src,"title":src});
      $("paletteAvatars").insert( img );
      img.observe("click",function(ev){
        $("avatar").value = ev.element().getAttribute("data-src");
        updateAvatar();
        $("avatarPickerPalette").hide();
      });
    }
  }
  regeneratePalette();
  $("avatarPicker").update("(<a href='#'>show picker</a>)")
  $("avatarPickerPalette").hide();
  $("avatarPicker").down("a").observe("click",function(ev){
    ev.stop();
    $("avatarPickerPalette").show();
  });

  for (var i=1; i<<?=$account->maxCDCs?>; i++)
  {
    if (!$("cdc"+i)) continue;
    new Autocompleter($("cdc"+i), {
      "dataUrl":"./ajax_prods.php",
      "width":320,
      "processRow": function(item) {
        var s = item.name.escapeHTML();
        if (item.groupName) s += " <small class='group'>" + item.groupName.escapeHTML() + "</small>";
        return s;
      }
    });
  }
});
//-->
</script>
<?
}

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
