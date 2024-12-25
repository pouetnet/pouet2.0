<?php
require_once("bootstrap.inc.php");

class PouetBoxUserMain extends PouetBox
{
  public $agreeRulez;
  public $agreeSucks;
  public $cdcProds;
  public $comments;
  public $credits;
  public $firstComments;
  public $groups;
  public $id;
  public $ims;
  public $lists;
  public $logos;
  public $nfos;
  public $paginator;
  public $parties;
  public $posts;
  public $prods;
  public $requests;
  public $sceneID;
  public $shots;
  public $show;
  public $topics;
  public $totalCreditsThumbDown;
  public $totalCreditsThumbUp;
  public $totalProds;
  public $totalRequests;
  public $user;
  public $topicCount;
  public $postCount;
  public $listCount;

  function __construct($id,$show)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_usermain";
    $this->title = "";
    $this->id = (int)$id;

    $this->paginator = new PouetPaginator();
    $this->show = $show;
  }

  function LoadFromDB()
  {
    $this->user = PouetUser::Spawn( $this->id );

    if (!$this->user) return;

    $this->user->UpdateGlops();
    $this->user->UpdateThumbs();

    $this->sceneID = $this->user->GetSceneIDData();

    $s = new BM_Query();
    $s->AddTable("users_im");
    $s->AddWhere(sprintf_esc("users_im.userID = %d",$this->id));
    $this->ims = $s->perform();

    $s = new BM_Query();
    $s->AddTable("users_cdcs");
    $s->AddWhere(sprintf_esc("users_cdcs.user = %d",$this->id));
    $s->Attach(array("users_cdcs"=>"cdc"),array("prods as prod"=>"id"));
    $s->AddOrder("users_cdcs_prod.id");
    $this->cdcProds = $s->perform();

    $this->logos = array();
    if ($this->show=="logos")
    {
      $this->logos = $this->GetLogosAdded( $this->show=="logos"? null : get_setting("userlogos") );
    }

    $this->prods = array();
    if ($this->show=="prods")
    {
      $this->prods = $this->GetProdsAdded( $this->show=="prods"? null : get_setting("userprods") );
    }

    $this->groups = array();
    if ($this->show=="groups")
    {
      $this->groups = $this->GetGroupsAdded( $this->show=="groups"? null : get_setting("usergroups") );
    }

    $this->parties = array();
    if ($this->show=="parties")
    {
      $this->parties = $this->GetPartiesAdded( $this->show=="parties"? null : get_setting("userparties") );
    }

    $this->shots = array();
    if ($this->show=="screenshots")
    {
      $this->shots = $this->GetScreenshotsAdded( $this->show=="screenshots"? null : get_setting("userscreenshots") );
    }

    $this->nfos = array();
    if ($this->show=="nfos")
    {
      $this->nfos = $this->GetNFOsAdded( $this->show=="nfos" ? null : get_setting("usernfos") );
    }

    $this->credits = array();
    if (!$this->show || $this->show=="credits")
    {
      $this->credits = $this->GetCredits( $this->show=="credits" ? null : 10 );
    }

    $this->firstComments = array();
    if (!$this->show/* || $this->show=="comments"*/)
    {
      //$this->firstComments = $this->GetFirstCommentsAdded( /*$this->show=="comments" ? null :*/ get_setting("usercomments") );
    }

    $this->topics = array();
    if ($this->show=="topics")
    {
      $this->topics = $this->GetBBSTopics( $this->show=="topics" ? null : get_setting("usercomments") );
    }

    $this->posts = array();
    if ($this->show=="posts")
    {
      $this->posts = $this->GetBBSPosts( $this->show=="posts" ? null : get_setting("usercomments") );
    }

    $this->lists = array();
    if ($this->show=="lists")
    {
      $this->lists = $this->GetLists( $this->show=="lists" ? null : 10 );
    }

    $this->requests = array();
    //if ($this->show=="requests")
    {
      $this->requests = $this->GetModRequests( $this->show=="requests" ? null : 1 );
    }

    $this->comments = array();
    if ($this->show=="demoblog")
    {
      $this->comments = $this->GetDemoblog( @$_GET["page"] );
    }

    $this->agreeRulez = array();
    if ($this->show=="otherstats")
    {
      $this->agreeRulez = $this->GetThumbAgreers( get_setting("userrulez"), 1 );
    }

    $this->agreeSucks = array();
    if ($this->show=="otherstats")
    {
      $this->agreeSucks = $this->GetThumbAgreers( get_setting("usersucks"), -1 );
    }
  }

  function AddRow($field, $value, $allowHTML = false)
  {
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
    echo "<img src='".POUET_CONTENT_URL."avatars/"._html($this->user->avatar)."' alt='avatar'/> ";
    echo "<span>"._html($this->user->nickname)."</span> information";

    if ($currentUser && $currentUser->id ==$this->user->id)
    {
      printf(" [<a href='account.php' class='adminlink'>edit profile</a>]\n");
    }
    if ($currentUser && $currentUser->IsModerator())
    {
      printf(" [<a href='admin_user_edit.php?who=%d' class='adminlink'>admin</a>]\n",$this->id);
    }

    echo " <span id='glops'><span>".$this->user->glops."</span> glöps</span>";
    echo "</h2>\n";
    return $s;
  }
  function GetLogosAdded( $limit = null )
  {
    $s = new BM_Query();
    $s->AddTable("logos");
    $s->AddField("logos.file");
    $s->AddField("logos.vote_count");
    $s->AddOrder("logos.vote_count DESC");
    $s->AddWhere(sprintf("logos.author1 = %d or logos.author2 = %d",$this->id,$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    $data = $s->perform();

    return $data;
  }
  function GetProdsAdded( $limit = null )
  {
    $s = new BM_Query("prods");
    $s->AddOrder("prods.addedDate desc");
    $s->AddWhere(sprintf("prods.addedUser = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    else
    {
      $this->paginator->SetData( "user.php?who=".$this->id."&show=prods", $this->user->stats["prods"], 50, @$_GET["page"], false );
      $this->paginator->SetLimitOnQuery( $s );
    }
    $data = $s->perform();
    PouetCollectPlatforms($data);

    return $data;
  }
  function GetGroupsAdded( $limit = null )
  {
    $s = new BM_Query("groups");
    $s->AddOrder("groups.addedDate desc");
    $s->AddWhere(sprintf("groups.addedUser = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    else
    {
      $this->paginator->SetData( "user.php?who=".$this->id."&show=groups", $this->user->stats["groups"], 50, @$_GET["page"], false );
      $this->paginator->SetLimitOnQuery( $s );
    }
    $data = $s->perform();

    return $data;
  }
  function GetPartiesAdded( $limit = null )
  {
    $s = new BM_Query("parties");
    $s->AddOrder("parties.addedDate desc");
    $s->AddWhere(sprintf("parties.addedUser = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    else
    {
      $this->paginator->SetData( "user.php?who=".$this->id."&show=parties", $this->user->stats["parties"], 50, @$_GET["page"], false );
      $this->paginator->SetLimitOnQuery( $s );
    }
    $data = $s->perform();

    return $data;
  }
  function GetScreenshotsAdded( $limit = null )
  {
    $s = new BM_Query("prods");
    $s->AddOrder("screenshots.added desc");
    $s->AddJoin("left","screenshots","prods.id = screenshots.prod");
    $s->AddWhere(sprintf("screenshots.user = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    else
    {
      $this->paginator->SetData( "user.php?who=".$this->id."&show=screenshots", $this->user->stats["screenshots"], 50, @$_GET["page"], false );
      $this->paginator->SetLimitOnQuery( $s );
    }
    $data = $s->perform();
    PouetCollectPlatforms($data);

    return $data;
  }
  function GetNFOsAdded( $limit = null )
  {
    $s = new BM_Query("prods");
    $s->AddOrder("nfos.added desc");
    $s->AddJoin("left","nfos","prods.id = nfos.prod");
    $s->AddWhere(sprintf("nfos.user = %d",$this->id));
    if ($limit)
      $s->SetLimit( $limit );
    else
    {
      $this->paginator->SetData( "user.php?who=".$this->id."&show=nfos", $this->user->stats["nfos"], 50, @$_GET["page"], false );
      $this->paginator->SetLimitOnQuery( $s );
    }
    $data = $s->perform();
    PouetCollectPlatforms($data);

    return $data;
  }
  function GetCredits( $limit = null )
  {
    $s = new BM_Query();
    $s->AddTable("credits");
    $s->AddField("credits.role");
    $s->Attach(array("credits"=>"prodID"), array("prods as prod"=>"id"));
    $s->AddWhere(sprintf("credits.userID = %d",$this->id));
    $s->AddOrder("credits_prod.releaseDate desc");
    if ($limit)
      $s->SetLimit( $limit );

    $data = $s->performWithCalcRows( $this->totalProds );

    $a = array();
    foreach($data as $v) $a[] = &$v->prod;
    PouetCollectPlatforms($a);
    PouetCollectAwards($a);

    $s = new BM_Query();
    $s->AddTable("credits");
    $s->AddField("sum(voteup) as up");
    $s->AddField("sum(votedown) as down");
    $s->Attach(array("credits"=>"prodID"), array("prods as prod"=>"id"));
    $s->AddWhere(sprintf("credits.userID = %d",$this->id));
    $s->AddOrder("credits_prod.addedDate desc");
    $data2 = $s->perform();

    $this->totalCreditsThumbUp = $data2[0]->up;
    $this->totalCreditsThumbDown = $data2[0]->down;

    return $data;
  }
  function GetFirstCommentsAdded( $limit = null )
  {
    $s = new BM_Query("prods");
    $s->AddField("comments.rating");
    $s->AddOrder("comments.addedDate desc");
    $s->AddJoin("left","comments","prods.id = comments.which");
    $s->AddWhere(sprintf("comments.who = %d",$this->id));
    $s->AddGroup("prods.id");
    if ($limit)
      $s->SetLimit( $limit );

    $data = $s->perform();
    PouetCollectPlatforms($data);

    return $data;
  }
  function GetThumbAgreers( $limit = null, $thumb = 1 )
  {
    $s = new BM_Query("");
    $s->AddField("count(*) as c");
    $s->AddTable("comments AS c1");
    $s->AddTable("comments AS c2");
    //$s->Attach(array("c1"=>"who"),array("users as u1"=>"id"));
    $s->Attach(array("c2"=>"who"),array("users as u2"=>"id"));
    $s->AddWhere(sprintf_esc("c1.rating = %d",$thumb));
    $s->AddWhere("c1.rating = c2.rating");
    $s->AddWhere("c1.which = c2.which");
    $s->AddWhere(sprintf_esc("c1.who = %d",$this->id));
    $s->AddWhere(sprintf_esc("c2.who != %d",$this->id));
    $s->AddGroup("c2.who");
    $s->AddOrder("c DESC");

    if ($limit)
      $s->SetLimit( $limit );

    $data = $s->perform();

    return $data;
  }
  function GetBBSTopics( $limit = null )
  {
    $s = new BM_Query();
    $s->AddTable("bbs_topics");
    $s->AddField("bbs_topics.id");
    $s->AddField("bbs_topics.topic");
    $s->AddField("bbs_topics.category");
    $s->AddWhere(sprintf("bbs_topics.userfirstpost = %d",$this->id));
    $s->AddOrder("bbs_topics.firstpost desc");
    if ($limit)
      $s->SetLimit( $limit );
    else
    {
      $this->topicCount = SQLLib::SelectRow( sprintf_esc("select count(*) as c from bbs_topics where bbs_topics.userfirstpost = %d",$this->id) )->c;

      $this->paginator->SetData( "user.php?who=".$this->id."&show=topics", $this->topicCount, 50, @$_GET["page"], false );
      $this->paginator->SetLimitOnQuery( $s );
    }

    $data = $s->perform();

    return $data;
  }
  function GetBBSPosts( $limit = null )
  {
    $s = new BM_Query();
    $s->AddTable("bbs_posts");
    $s->AddField("bbs_posts.id as postID");
    $s->AddJoin("left","bbs_topics","bbs_topics.id = bbs_posts.topic");
    $s->AddField("bbs_topics.id");
    $s->AddField("bbs_topics.topic");
    $s->AddField("bbs_topics.category");
    $s->AddWhere(sprintf("bbs_posts.author = %d",$this->id));
    $s->AddOrder("bbs_posts.added desc");
    if ($limit)
    {
      $s->SetLimit( $limit );
      $s->AddGroup("bbs_topics.id");
    }
    else
    {
      $this->postCount = SQLLib::SelectRow( sprintf_esc("select count(*) as c from bbs_posts where bbs_posts.author = %d",$this->id) )->c;

      $this->paginator->SetData( "user.php?who=".$this->id."&show=posts", $this->postCount, 50, @$_GET["page"], false );
      $this->paginator->SetLimitOnQuery( $s );
    }

    $data = $s->perform();

    return $data;
  }
  function GetLists( $limit = null )
  {
    $s = new BM_Query();
    $s->AddTable("lists");
    $s->AddField("lists.id as listID");
    $s->AddField("lists.name");
    $s->AddWhere(sprintf("lists.owner = %d",$this->id));
    $s->AddOrder("lists.addedDate desc");
    if ($limit)
    {
      $s->SetLimit( $limit );
    }
    else
    {
      $this->listCount = SQLLib::SelectRow( sprintf_esc("select count(*) as c from lists where owner = %d",$this->id) )->c;

      $this->paginator->SetData( "user.php?who=".$this->id."&show=lists", $this->listCount, 50, @$_GET["page"], false );
      $this->paginator->SetLimitOnQuery( $s );
    }

    $data = $s->perform();

    return $data;
  }
  function GetModRequests( $limit = null )
  {
    $s = new BM_Query();
    $s->AddTable("modification_requests");
    $s->AddWhere(sprintf("modification_requests.userID = %d",$this->id));
    $s->AddOrder("modification_requests.requestDate desc");
    if ($limit)
      $s->SetLimit( $limit );

    $data = $s->performWithCalcRows( $this->totalRequests );

    return $data;
  }

  function GetDemoblog( $page )
  {
    $s = new BM_Query();
    $s->AddTable("comments");
    $s->AddField("count(*) as c");
    $s->AddWhere(sprintf("comments.who = %d",$this->id));
    $this->postCount = SQLLib::SelectRow($s->GetQuery())->c;

    $s = new BM_Query();
    $s->AddTable("comments");
    $s->AddField("comments.rating");
    $s->AddField("comments.id as commentID");
    $s->AddField("comments.addedDate as commentDate");
    $s->AddField("comments.comment");
    $s->AddOrder("comments.addedDate desc");
    //$s->AddJoin("left","comments","prods.id = comments.which");
    $s->Attach(array("comments"=>"which"),array("prods as prod"=>"id"));
    $s->AddWhere(sprintf_esc("comments.who = %d",$this->id));
    if (@$_GET["nothumbsup"]) $s->AddWhere("comments.rating != 1");
    if (@$_GET["nopiggies"]) $s->AddWhere("comments.rating != 0");
    if (@$_GET["nothumbsdown"]) $s->AddWhere("comments.rating != -1");

    $limit = 10;
    if (@$_GET["com"]) $limit = (int)$_GET["com"];
    $limit = min($limit,100);
    $limit = max($limit,1);
    if (@$_GET["com"]==-1) $limit = $this->postCount;

    $this->paginator->SetData( "user.php?who=".$this->id."&show=demoblog", $this->postCount, $limit, $page, false );
    $this->paginator->SetLimitOnQuery( $s );

    $data = $s->perform();
    $prods = array(); foreach($data as $v) $prods[] = &$v->prod;
    PouetCollectPlatforms( $prods );

    return $data;
  }

  function RenderBody()
  {
    global $currentUser;
    $s = "";
    echo "<div class='content'>\n";
    echo "<div class='bigavatar'><img src='".POUET_CONTENT_URL."avatars/"._html($this->user->avatar)."' alt='big avatar'/></div>\n";
    echo "<ul id='userdata'>\n";

    echo "<li class='header'>general:</li>\n";
    //echo $this->AddRow("first name",$this->sceneID["login"]);
    echo $this->AddRow("level",$this->user->level);

    echo "<li class='header'>personal:</li>\n";
    echo $this->AddRow("first name",@$this->sceneID["first_name"]);
    echo $this->AddRow("last name",@$this->sceneID["last_name"]);
    //echo $this->AddRow("country",$this->sceneID["country"]);

    if ($currentUser)
    {
      global $IM_TYPES;
      foreach($this->ims as $im)
      {
        if ($im->im_type && @$IM_TYPES[$im->im_type] && @$IM_TYPES[$im->im_type]["display"] && preg_match("/".$IM_TYPES[$im->im_type]["capture"]."/",$im->im_id))
        {
          $func = $IM_TYPES[$im->im_type]["display"];
          $imID = $func($im->im_id);
          $this->AddRow( $im->im_type, $imID, true );
        }
        else
        {
          $this->AddRow( $im->im_type, $im->im_id );
        }
      }
    }

    if ($this->user->csdb || $this->user->slengpung || $this->user->zxdemo || $this->user->demozoo)
    {
      echo "<li class='header'>portals:</li>\n";
      if ($this->user->csdb)
        echo $this->AddRow("csdb","<a href='http://csdb.dk/scener/?id=".$this->user->csdb."'>profile</a>",true);
      if ($this->user->slengpung)
        echo $this->AddRow("slengpung","<a href='http://www.slengpung.com/?userid=".$this->user->slengpung."'>pictures</a>",true);
      if ($this->user->zxdemo)
        echo $this->AddRow("zxdemo","<a href='http://zxdemo.org/author.php?id=".$this->user->zxdemo."'>profile</a>",true);
      if ($this->user->demozoo)
        echo $this->AddRow("demozoo","<a href='http://demozoo.org/sceners/".$this->user->demozoo."/'>profile</a>",true);
    }

    if ($this->cdcProds)
    {
      echo "<li class='header'>cdcs:</li>\n";
      $x = 1;
      foreach($this->cdcProds as $v)
        if ($v->prod)
          $this->AddRow("cdc #".($x++),$v->prod->RenderSingleRow(),true);
    }

    echo "</ul>\n";
    echo "</div>\n";

    if ($this->credits && $this->totalProds)
    {
      echo "<div class='contribheader'>contributions to prods ";
      echo "<span>".$this->totalProds." prods (".$this->user->thumbups." thumbs up, ".$this->user->thumbdowns." thumbs down)</span>";
      if ($this->show!="credits")
        echo " [<a href='user.php?who=".$this->id."&amp;show=credits'>show all</a>]";
      echo "</div>\n";
      echo "<ul class='boxlist' id='contriblist'>";
      foreach($this->credits as $p)
      {
        echo "<li>";
        if ($p->prod->releaseDate)
        {
          echo "<span class='releaseYear'>".substr($p->prod->releaseDate,0,4)."</span> ";
        }
        echo $p->prod->RenderTypeIcons();
        echo $p->prod->RenderPlatformIcons();
        echo $p->prod->RenderSingleRow()." ";
        echo $p->prod->RenderAccolades();
        echo " [".$p->role."]";
        echo "</li>";
      }
      echo "</ul>";
    }

    if (!$this->show && $this->user->stats["ud"])
      echo "<div class='contribheader'>United Devices contribution <span>".$this->user->stats["ud"]." glöps</span></div>\n";

    if ($this->user->stats["logos"])
    {
      if (!$this->show || $this->logos)
      {
        echo "<div class='contribheader'>logos added <span>".$this->user->stats["logosVote"]." x 20 = ".($this->user->stats["logosVote"] * 20)." glöps - downvoted logos don't get glöps</span>";
        if ($this->show!="logos")
          echo " [<a href='user.php?who=".$this->id."&amp;show=logos'>show</a>]";
        echo "</div>\n";
      }
      if ($this->logos)
      {
        echo "<ul class='boxlist' id='logolist'>";
        foreach($this->logos as $l)
        {
          echo "<li>";
          echo "<div class='logo'>";
          echo "<img src='".POUET_CONTENT_URL."logos/"._html($l->file)."' alt=''/>";
          echo "<span class='logovotes'>current votes: "._html($l->vote_count)."</span>";
          echo "</div>";
          echo "</li>";
        }
        echo "</ul>";
      }
    }

    if ($this->user->stats["prods"])
    {
      if (!$this->show || $this->prods)
      {
        echo "<div class='contribheader'>prods added <span>".$this->user->stats["prods"]." x 2 = ".($this->user->stats["prods"] * 2)." glöps</span> ";
        if ($this->show!="prods")
          echo "[<a href='user.php?who=".$this->id."&amp;show=prods'>show</a>]";
        echo "</div>\n";
      }
      if ($this->prods)
      {
        echo "<ul class='boxlist'>";
        foreach($this->prods as $p)
        {
          echo "<li>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAccolades();
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
    }

    if ($this->user->stats["groups"])
    {
      if (!$this->show || $this->groups)
      {
        echo "<div class='contribheader'>groups added <span>".$this->user->stats["groups"]." glöps</span> ";
        if ($this->show!="groups")
          echo "[<a href='user.php?who=".$this->id."&amp;show=groups'>show</a>]";
        echo "</div>\n";
      }
      if ($this->groups)
      {
        echo "<ul class='boxlist'>";
        foreach($this->groups as $g)
        {
          echo "<li>";
          echo $g->RenderLong();
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
    }

    if ($this->user->stats["parties"])
    {
      if (!$this->show || $this->parties)
      {
        echo "<div class='contribheader'>parties added <span>".$this->user->stats["parties"]." glöps</span> ";
        if ($this->show!="parties")
          echo "[<a href='user.php?who=".$this->id."&amp;show=parties'>show</a>]";
        echo "</div>\n";
      }
      if ($this->parties)
      {
        echo "<ul class='boxlist'>";
        foreach($this->parties as $p)
        {
          echo "<li>";
          echo $p->PrintLinked();
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
    }

    if ($this->user->stats["screenshots"])
    {
      if (!$this->show || $this->shots)
      {
        echo "<div class='contribheader'>screenshots added <span>".$this->user->stats["screenshots"]." glöps</span> ";
        if ($this->show!="screenshots")
          echo "[<a href='user.php?who=".$this->id."&amp;show=screenshots'>show</a>]";
        echo "</div>\n";
      }
      if ($this->shots)
      {
        echo "<ul class='boxlist'>";
        foreach($this->shots as $p)
        {
          echo "<li>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAccolades();
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
    }

    if ($this->user->stats["nfos"])
    {
      if (!$this->show || $this->nfos)
      {
        echo "<div class='contribheader'>nfos added <span>".$this->user->stats["nfos"]." glöps</span> ";
        if ($this->show!="nfos")
          echo "[<a href='user.php?who=".$this->id."&amp;show=nfos'>show</a>]";
        echo "</div>\n";
      }
      if ($this->nfos)
      {
        echo "<ul class='boxlist'>";
        foreach($this->nfos as $p)
        {
          echo "<li>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAccolades();
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
    }
    if ($this->user->stats["comments"])
    {
      if (!$this->show || $this->firstComments)
      {
        echo "<div class='contribheader'>comments <span>".$this->user->stats["comments"]." glöps</span>";
        //echo " [<a href='user.php?who=".$this->id."&amp;show=comments'>show</a>]";
        if ($this->show!="demoblog")
          echo " [<a href='user.php?who=".$this->id."&amp;show=demoblog'>demoblog</a>]";
        if ($this->show!="otherstats")
          echo " [<a href='user.php?who=".$this->id."&amp;show=otherstats'>other stats</a>]";
        echo "</div>\n";
      }
      if ($this->firstComments)
      {
        echo "<ul class='boxlist'>";
        foreach($this->firstComments as $p)
        {
          $rating = $p->rating>0 ? "rulez" : ($p->rating<0 ? "sucks" : "isok");
          echo "<li>";
          echo "<span class='vote ".$rating."'>".$rating."</span>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAccolades();
          echo "</li>";
        }
        echo "</ul>";
      }
    }

    if ($this->user->stats["requestGlops"])
    {
      if (!$this->show)
      {
        echo "<div class='contribheader'>requests made <span>".(int)$this->totalRequests." requests, ".$this->user->stats["requestGlops"]." glöps</span> ";
        //if ($this->show!="requests")
        //  echo "[<a href='user.php?who=".$this->id."&amp;show=requests'>show</a>]";
        echo "</div>\n";
      }
      /*
      if ($this->requests)
      {
        echo "<ul class='boxlist'>";
        foreach($this->nfos as $p)
        {
          echo "<li>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAccolades();
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
      */
    }

    if ($this->user->stats["oneliners"])
    {
      if (!$this->show)
      {
        echo "<div class='contribheader'>oneliners posted <span>".(int)$this->user->stats["oneliners"]." oneliners</span> ";
        echo "[<a href='oneliner.php?who=".$this->id."'>show</a>]";
        echo "</div>\n";
      }
      /*
      if ($this->requests)
      {
        echo "<ul class='boxlist'>";
        foreach($this->nfos as $p)
        {
          echo "<li>";
          echo $p->RenderTypeIcons();
          echo $p->RenderPlatformIcons();
          echo $p->RenderSingleRow();
          echo $p->RenderAccolades();
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
      */
    }

    if ($this->user->stats["topics"])
    {
      if (!$this->show || $this->topics)
      {
        echo "<div class='contribheader'>bbs topics opened";
        if ($this->user->stats["topics"])
          echo " <span>".$this->user->stats["topics"]." topics</span>";
        if ($this->show!="topics")
          echo " [<a href='user.php?who=".$this->id."&amp;show=topics'>show</a>]";
        echo "</div>\n";
      }
      if ($this->topics)
      {
        echo "<ul class='boxlist'>";
        foreach($this->topics as $t)
        {
          echo "<li>";
          echo "<a href='topic.php?which=".$t->id."'>"._html($t->topic)."</a> ("._html($t->category).")";
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
    }

    if ($this->user->stats["posts"])
    {
      if (!$this->show || $this->posts)
      {
        echo "<div class='contribheader'>bbs posts";
        if ($this->user->stats["posts"])
          echo " <span>".$this->user->stats["posts"]." posts</span>";
        if ($this->show!="posts")
          echo " [<a href='user.php?who=".$this->id."&amp;show=posts'>show</a>]";
        echo "</div>\n";
      }
      if ($this->posts)
      {
        echo "<ul class='boxlist'>";
        foreach($this->posts as $p)
        {
          echo "<li>";
          //echo "<a href='topic.php?which=".$p->id."'>"._html($p->topic)."</a> ("._html($p->category).")";
          echo "<a href='topic.php?post=".$p->postID."'>"._html($p->topic)."</a> ("._html($p->category).")";
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
    }

    if ($this->user->stats["lists"])
    {
      if (!$this->show || $this->lists)
      {
        echo "<div class='contribheader'>lists";
        if ($this->user->stats["lists"])
          echo " <span>".$this->user->stats["lists"]." lists</span>";
        if ($this->show!="lists")
          echo " [<a href='user.php?who=".$this->id."&amp;show=lists'>show</a>]";
        echo "</div>\n";
      }
      if ($this->lists)
      {
        echo "<ul class='boxlist'>";
        foreach($this->lists as $l)
        {
          echo "<li>";
          echo "<a href='lists.php?which=".$l->listID."'>"._html($l->name)."</a>";
          echo "</li>";
        }
        echo "</ul>";
        $this->paginator->RenderNavbar();
      }
    }

    if ($this->show=="otherstats")
    {
      echo "<div class='contribheader'>top thumb up agreers";
      echo "</div>\n";
      if ($this->agreeRulez)
      {
        echo "<ul class='boxlist'>";
        foreach($this->agreeRulez as $p)
        {
          echo "<li>";
          echo $p->u2->PrintLinkedAvatar()." ";
          echo $p->u2->PrintLinkedName()." ";
          echo "(".$p->c." prods)";
          echo "</li>";
        }
        echo "</ul>";
      }

      echo "<div class='contribheader'>top thumb down agreers";
      echo "</div>\n";
      if ($this->agreeSucks)
      {
        echo "<ul class='boxlist'>";
        foreach($this->agreeSucks as $p)
        {
          echo "<li>";
          echo $p->u2->PrintLinkedAvatar()." ";
          echo $p->u2->PrintLinkedName()." ";
          echo "(".$p->c." prods)";
          echo "</li>";
        }
        echo "</ul>";
      }
    }

    if ($this->comments)
    {
      echo "<ul class='boxlist' id='demoblog'>";
      foreach($this->comments as $c)
      {
        $p = $c->prod;
        if (!$p) continue;
        $rating = $c->rating>0 ? "rulez" : ($c->rating<0 ? "sucks" : "isok");
        echo "<li class='blogprod'>";
        echo $p->RenderTypeIcons();
        echo $p->RenderPlatformIcons();
        echo $p->RenderSingleRow();
        echo "</li>";
        echo "<li class='blogcomment'>";
        echo parse_message( $c->comment );
        echo "</li>";
        echo "<li class='blogvote'>";
        echo "<span class='vote ".$rating."'>".$rating."</span>";
        echo "added on the <a href='prod.php?post=".$c->commentID."'>".$c->commentDate."</a>";
        echo "</li>";
      }
      echo "</ul>";
      $this->paginator->RenderNavbar();
    }

  }

  function RenderFooter()
  {
    echo "  <div class='foot'>account created on the ".$this->user->registerDate."</div>\n";
    echo "</div>\n";
  }
};

$p = new PouetBoxUserMain( (int)@$_GET["who"], @$_GET["show"] );
$p->Load();

if ($p->user)
  $TITLE = $p->user->nickname;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

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

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
