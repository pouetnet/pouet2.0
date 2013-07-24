<?
include_once("bootstrap.inc.php");
include_once("include_generic/recaptchalib.php");

class PouetBoxUserMain extends PouetBox
{
  function PouetBoxUserMain($id) {
    parent::__construct();
    $this->uniqueID = "pouetbox_usermain";
    $this->title = "";
    $this->id = (int)$id;
  }

  function LoadFromDB() {
    $s = new BM_Query("users");
    $s->AddWhere("users.id=".$this->id);
    $s->AddExtendedFields();
//    foreach(PouetUser::getExtendedFields() as $v)
//      $s->AddField("users.".$v);
    $this->user = $s->perform();
    $this->user = reset($this->user);
    if (!$this->user) return;

    $this->user->UpdateGlops();

    $this->sceneID = $this->user->GetSceneIDData();

    $this->cdcs = array();
    $rows = SQLLib::SelectRows(sprintf_esc("select cdc from users_cdcs where user=%d",$this->id));
    foreach($rows as $r)
      $this->cdcs[] = $r->cdc;

    if ($this->cdcs)
    {
      $s = new BM_Query("prods");
      $s->AddWhere(sprintf_esc("prods.id in (%s)",implode(",",$this->cdcs)));
      $s->AddOrder("prods.id");
      $this->cdcProds = $s->perform();
    }

  }

  function AddRow($field, $value, $allowHTML = false) {
    $s = "";
    if ($value) {
      echo "<li>\n";
      echo " <span class='field'>".$field.":</span>\n";
      echo " ".($allowHTML ? $value : _html($value))."\n";
      echo "</li>\n";
    }
    return $s;
  }

  function RenderHeader() 
  {
    global $currentUser;
    
    $s = "";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    echo " <h2>";
    echo "<img src='".POUET_CONTENT_URL."/avatars/"._html($this->user->avatar)."'/> ";
    echo "<span>"._html($this->user->nickname)."</span> information";

    if ($currentUser && $currentUser->IsAdministrator())
    {
      printf(" [<a href='admin_user_edit.php?who=%d' class='adminlink'>edit</a>]\n",$this->id);
    }

    echo " <span id='glops'><span>".$this->user->glops."</span> glöps</span>";
    echo "</h2>\n";
    return $s;
  }
  function GetLogosAdded( $limit = null )
  {
    $s = new BM_Query("logos");
    $s->AddField("logos.file");
    $s->AddField("logos.vote_count");
    $s->AddWhere(sprintf("logos.author1 = %d or logos.author2 = %d",$this->id,$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    $data = $s->perform();

    return $data;
  }
  function GetProdsAdded( $limit = null )
  {
    $s = new BM_Query("prods");
    $s->AddOrder("prods.quand desc");
    $s->AddWhere(sprintf("prods.added = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    $data = $s->perform();
    PouetCollectPlatforms($data);

    return $data;
  }
  function GetGroupsAdded( $limit = null )
  {
    $s = new BM_Query("groups");
    $s->AddOrder("groups.quand desc");
    $s->AddWhere(sprintf("groups.added = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    $data = $s->perform();

    return $data;
  }
  function GetPartiesAdded( $limit = null )
  {
    $s = new BM_Query("parties");
    $s->AddOrder("parties.quand desc");
    $s->AddWhere(sprintf("parties.added = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    $data = $s->perform();

    return $data;
  }
  function GetScreenshotsAdded( $limit = null )
  {
    $s = new BM_Query("prods");
    $s->AddOrder("prods.quand desc");
    $s->AddJoin("left","screenshots","prods.id = screenshots.prod");
    $s->AddWhere(sprintf("screenshots.user = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    $data = $s->perform();
    PouetCollectPlatforms($data);

    return $data;
  }
  function GetNFOsAdded( $limit = null )
  {
    $s = new BM_Query("prods");
    $s->AddOrder("prods.quand desc");
    $s->AddJoin("left","nfos","prods.id = nfos.prod");
    $s->AddWhere(sprintf("nfos.user = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    $data = $s->perform();
    PouetCollectPlatforms($data);

    return $data;
  }
  function GetFirstCommentsAdded( $limit = null )
  {
    $s = new BM_Query("prods");
    $s->AddField("comments.rating");
    $s->AddOrder("comments.quand desc");
    $s->AddJoin("left","comments","prods.id = comments.which");
    $s->AddWhere(sprintf("comments.who = %d",$this->id));
    $s->AddGroup("prods.id");
    if ($limit)
      $s->SetLimit( $limit );
    
    $data = $s->perform();
    PouetCollectPlatforms($data);

    return $data;
  }
  function GetBBSTopics( $limit = null )
  {
    $s = new BM_Query("bbs_topics");
    $s->AddField("bbs_topics.id");
    $s->AddField("bbs_topics.topic");
    $s->AddField("bbs_topics.category");
    $s->AddWhere(sprintf("bbs_topics.userfirstpost = %d",$this->id));
    $s->AddOrder("bbs_topics.firstpost desc");
    if ($limit)
      $s->SetLimit( $limit );
    
    $data = $s->perform();

    return $data;
  }
  function GetBBSPosts( $limit = null )
  {
    $s = new BM_Query("bbs_posts");
    $s->AddJoin("left","bbs_topics","bbs_topics.id = bbs_posts.topic");
    $s->AddField("bbs_topics.id");
    $s->AddField("bbs_topics.topic");
    $s->AddField("bbs_topics.category");
    $s->AddWhere(sprintf("bbs_posts.author = %d",$this->id));
    $s->AddGroup("bbs_topics.id");
    $s->AddOrder("bbs_posts.added desc");
    if ($limit)
      $s->SetLimit( $limit );
    
    $data = $s->perform();

    return $data;
  }
  function GetCommentsAdded( $limit, $page = 1 )
  {
    $this->perPage = $limit;
    $this->page = $page ? $page : 1;
    
    $s = new BM_Query("comments");
    $s->AddField("comments.rating");
    $s->AddField("comments.quand as commentDate");
    $s->AddField("comments.comment");
    $s->AddOrder("comments.quand desc");
    //$s->AddJoin("left","comments","prods.id = comments.which");
    $s->Attach(array("comments"=>"which"),array("prods as prod"=>"id"));
    $s->AddWhere(sprintf("comments.who = %d",$this->id));
//    $s->SetLimit( $limit, $offset );
    $s->SetLimit( $this->perPage, (int)(($this->page-1) * $this->perPage) );
    
    $data = $s->performWithCalcRows( $this->commentCount );
    PouetCollectPlatforms($data);

    $this->numPages = (int)ceil($this->commentCount / $this->perPage);

    return $data;
  }
  function RenderNavbar() {
    echo "<div class='navbar'>\n";
    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='user.php?who=".$this->id."&amp;show=demoblog&amp;page=".($this->page - 1)."'>previous page</a></div>\n";
    if ($this->page * $this->perPage < $this->commentCount)
      echo "  <div class='nextpage'><a href='user.php?who=".$this->id."&amp;show=demoblog&amp;page=".($this->page + 1)."'>next page</a></div>\n";
    echo "  <div class='selector'>";
    echo "  <form action='user.php' method='get'>\n";
    echo "   go to page <select name='page'>\n";
    
    for ($x = 1; $x <= $this->numPages; $x++)
      echo "      <option value='".$x."'".($x==$this->page?" selected='selected'":"").">".$x."</option>\n";
      
    echo "   </select> of ".$this->numPages."\n";
    echo "  <input type='hidden' name='who' value='".$this->id."'/>\n";
    echo "  <input type='hidden' name='show' value='demoblog'/>\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "  </form>\n";
    echo "  </div>\n";
    echo "</div>\n";
    return $s;
  }


  function RenderBody() {
    $s = "";
    echo "<div class='content'>\n";
    echo "<div class='bigavatar'><img src='".POUET_CONTENT_URL."/avatars/"._html($this->user->avatar)."'/></div>\n";
    echo "<ul id='userdata'>\n";

    echo "<li class='header'>general:</li>\n";
    //echo $this->AddRow("first name",$this->sceneID["login"]);
    echo $this->AddRow("level",$this->user->level);

    echo "<li class='header'>personal:</li>\n";
    echo $this->AddRow("first name",$this->sceneID["firstname"]);
    echo $this->AddRow("last name",$this->sceneID["lastname"]);
    echo $this->AddRow("country",$this->sceneID["country"]);

    if ($this->sceneID["email"])
    {
      if ($this->sceneID["hidden"]=="yes")
      {
        echo $this->AddRow("email","<span style='color:#9999AA'>hidden</span>",true);
      }
      else
      {
        echo $this->AddRow("email",recaptcha_mailhide_html(CAPTCHA_MAILHIDE_PUBLICKEY,CAPTCHA_MAILHIDE_PRIVATEKEY,$this->sceneID["email"]),true);
      }
    }
    if ($this->sceneID["url"]) {
      $site = _html($this->sceneID["url"]);
      if (substr($site,0,7)!="http://")
        $site = "http://".$site;
      echo $this->AddRow("website","<a href='".$site."'>".$site."</a>",true);
    }

    if ($this->user->im_type)
      $this->AddRow($this->user->im_type,$this->user->im_id);

    echo "<li class='header'>portals:</li>\n";
    if ($this->user->csdb)
      echo $this->AddRow("csdb","<a href='http://csdb.dk/scener/?id=".$this->user->csdb."'>profile</a>",true);
    if ($this->user->slengpung)
      echo $this->AddRow("slengpung","<a href='http://www.slengpung.com/?userid=".$this->user->slengpung."'>pictures</a>",true);
    if ($this->user->zxdemo)
      echo $this->AddRow("zxdemo","<a href='http://zxdemo.org/author.php?id=".$this->user->zxdemo."'>profile</a>",true);

    if ($this->cdcProds)
    {
      echo "<li class='header'>cdcs:</li>\n";
      $x = 1;
      foreach($this->cdcProds as $v)
        $this->AddRow("cdc #".($x++),$v->RenderLink().($v->groups?" by ".$v->RenderGroupsLong():""),true);
    }

    echo "</ul>\n";
    echo "</div>\n";

    if (!$_GET["show"] && $this->user->stats["ud"])
      echo "<div class='contribheader'>United Devices contribution <span>".$this->user->stats["ud"]." glöps</span></div>\n";
      
    if (!$_GET["show"] || $_GET["show"]=="logos")
    {
      $logos = $this->GetLogosAdded( $_GET["show"]=="logos"? null : get_setting("userlogos") );
      if ($logos)
      {
        echo "<div class='contribheader'>latest added logos <span>".$this->user->stats["logos"]." x 20 = ".($this->user->stats["logos"] * 20)." glöps - downvoted logos don't get glöps</span></div>\n";
        echo "<ul class='boxlist' id='logolist'>";
        foreach($logos as $l)
        {
          echo "<li>";
          echo "<div class='logo'>"; 
          echo "<img src='".POUET_CONTENT_URL."gfx/logos/"._html($l->file)."' alt=''/>";
          echo "<span class='logovotes'>current votes: "._html($l->vote_count)."</span>";
          echo "</div>"; 
          echo "</li>";
        }
        echo "</ul>";
      }
    }

    if (!$_GET["show"] || $_GET["show"]=="prods")
    {
      $prods = $this->GetProdsAdded( $_GET["show"]=="prods"? null : get_setting("userprods") );
      if ($prods)
      {
        echo "<div class='contribheader'>latest added prods <span>".$this->user->stats["prods"]." x 2 = ".($this->user->stats["prods"] * 2)." glöps</span> [<a href='user.php?who=".$this->id."&amp;show=prods'>show all</a>]</div>\n";
        echo "<ul class='boxlist'>";
        foreach($prods as $p)
        {
          echo "<li>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAwards();
          echo "</li>";
        }
        echo "</ul>";
      }
    }

    if (!$_GET["show"] || $_GET["show"]=="groups")
    {
      $groups = $this->GetGroupsAdded( $_GET["show"]=="groups"? null : get_setting("usergroups") );
      if ($groups)
      {
        echo "<div class='contribheader'>latest added groups <span>".$this->user->stats["groups"]." glöps</span> [<a href='user.php?who=".$this->id."&amp;show=groups'>show all</a>]</div>\n";
        echo "<ul class='boxlist'>";
        foreach($groups as $g)
        {
          echo "<li>";
          echo $g->RenderLong();
          echo "</li>";
        }
        echo "</ul>";
      }
    }

    if (!$_GET["show"] || $_GET["show"]=="parties")
    {
      $parties = $this->GetPartiesAdded( $_GET["show"]=="parties"? null : get_setting("userparties") );
      if ($parties)
      {
        echo "<div class='contribheader'>latest added parties <span>".$this->user->stats["parties"]." glöps</span> [<a href='user.php?who=".$this->id."&amp;show=parties'>show all</a>]</div>\n";
        echo "<ul class='boxlist'>";
        foreach($parties as $p)
        {
          echo "<li>";
          echo $p->PrintLinked();
          echo "</li>";
        }
        echo "</ul>";
      }
    }

    if (!$_GET["show"] || $_GET["show"]=="screenshots")
    {
      $shots = $this->GetScreenshotsAdded( $_GET["show"]=="screenshots"? null : get_setting("userscreenshots") );
      if ($shots)
      {
        echo "<div class='contribheader'>latest added screenshots <span>".$this->user->stats["screenshots"]." glöps</span> [<a href='user.php?who=".$this->id."&amp;show=screenshots'>show all</a>]</div>\n";
        echo "<ul class='boxlist'>";
        foreach($shots as $p)
        {
          echo "<li>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAwards();
          echo "</li>";
        }
        echo "</ul>";
      }
    }

    if (!$_GET["show"] || $_GET["show"]=="nfos")
    {
      $nfos = $this->GetNFOsAdded( $_GET["show"]=="nfos" ? null : get_setting("usernfos") );
      if ($nfos)
      {
        echo "<div class='contribheader'>latest added nfos <span>".$this->user->stats["nfos"]." glöps</span> [<a href='user.php?who=".$this->id."&amp;show=nfos'>show all</a>]</div>\n";
        echo "<ul class='boxlist'>";
        foreach($nfos as $p)
        {
          echo "<li>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAwards();
          echo "</li>";
        }
        echo "</ul>";
      }
    }
    if (!$_GET["show"] || $_GET["show"]=="comments")
    {
      $comments = $this->GetFirstCommentsAdded( $_GET["show"]=="comments" ? null : get_setting("usercomments") );
      if ($comments)
      {
        echo "<div class='contribheader'>latest 1st comments <span>".$this->user->stats["comments"]." glöps</span>";
        echo " [<a href='user.php?who=".$this->id."&amp;show=comments'>show all</a>]";
        echo " [<a href='user.php?who=".$this->id."&amp;show=demoblog'>demoblog</a>]";
        echo "</div>\n";
        echo "<ul class='boxlist'>";
        foreach($comments as $p)
        {
          $rating = $p->rating>0 ? "rulez" : ($p->rating<0 ? "sucks" : "isok");
          echo "<li>";
          echo "<span class='vote ".$rating."'>".$rating."</span>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAwards();
          echo "</li>";
        }
        echo "</ul>";
      }
    }
    if (!$_GET["show"] || $_GET["show"]=="topics")
    {
      $topics = $this->GetBBSTopics( $_GET["show"]=="topics" ? null : get_setting("usercomments") );
      if ($topics)
      {
        echo "<div class='contribheader'>latest bbs topics [<a href='user.php?who=".$this->id."&amp;show=topics'>show all</a>]</div>\n";
        echo "<ul class='boxlist'>";
        foreach($topics as $t)
        {
          echo "<li>";
          echo "<a href='topic.php?which=".$t->id."'>"._html($t->topic)."</a> ("._html($t->category).")";
          echo "</li>";
        }
        echo "</ul>";
      }
    }
    if (!$_GET["show"] || $_GET["show"]=="posts")
    {
      $posts = $this->GetBBSPosts( $_GET["show"]=="posts" ? null : get_setting("usercomments") );
      if ($posts)
      {
        echo "<div class='contribheader'>latest bbs posts [<a href='user.php?who=".$this->id."&amp;show=posts'>show all</a>]</div>\n";
        echo "<ul class='boxlist'>";
        foreach($posts as $p)
        {
          echo "<li>";
          echo "<a href='topic.php?which=".$p->id."'>"._html($p->topic)."</a> ("._html($p->category).")";
          echo "</li>";
        }
        echo "</ul>";
      }
    }
    if ($_GET["show"]=="demoblog")
    {
      $comments = $this->GetCommentsAdded( 10, $_GET["page"] );
      if ($comments)
      {
        echo "<ul class='boxlist' id='demoblog'>";
        foreach($comments as $c)
        {
          $p = $c->prod;
          $rating = $c->rating>0 ? "rulez" : ($c->rating<0 ? "sucks" : "isok");
          echo "<li class='blogprod'>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo "<span class='prod'>".$p->RenderLink()."</span>\n";
          echo "</li>";
          echo "<li class='blogcomment'>";
          echo parse_message( $c->comment );
          echo "</li>";
          echo "<li class='blogvote'>";
          echo "<span class='vote ".$rating."'>".$rating."</span>";
          echo "added on the ".$c->commentDate;
          echo "</li>";
        }
        echo "</ul>";
      }
      $this->RenderNavbar();
    }


    return $s;
  }

  function RenderFooter() {
    echo "  <div class='foot'>account created on the ".$this->user->quand."</div>\n";
    echo "</div>\n";
  }
};

$userid = (int)$_GET["who"];

$p = new PouetBoxUserMain($userid);
$p->Load();

if ($p->user)
  $TITLE = $p->user->nickname;

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if ($p->user)
{
  echo $p->Render();
}
else
{
  echo "tüzesen süt le a nyári nap sugára / az ég tetejéről a juhászbojtárra.";
}

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>
