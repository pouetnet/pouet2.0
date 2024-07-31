<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-bbs-post.php");
require_once("include_pouet/box-modalmessage.php");

$POSTS_PER_PAGE = max(1,get_setting("topicposts"));

if (@$_GET["post"]) // setting-independent post lookup
{
  $topic = SQLLib::SelectRow(sprintf_esc("select topic from bbs_posts where id = %d",$_GET["post"]));
  if ($topic && $topic->topic)
  {
    $topicID = $topic->topic;
    $inner = sprintf_esc("select id, @rowID:=@rowID+1 as rowID from bbs_posts, (SELECT @rowID:=0) as init where topic = %d",$topicID);
    $row = SQLLib::SelectRow(sprintf_esc("select * from (".$inner.") as t where id = %d",$_GET["post"]));

    redirect(sprintf("topic.php?which=%d&page=%d#c%d",$topicID,(int)(($row->rowID - 1) / $POSTS_PER_PAGE) + 1,$_GET["post"]));
    exit();
  }
}

class PouetBoxBBSView extends PouetBox
{
  public $topic;
  public $posts;
  public $id;
  public $page;
  public $postcount;
  public $paginator;
  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_bbsview";
    $this->title = "comments";
    $this->id = (int)$id;
  }

  function LoadFromDB()
  {
    global $POSTS_PER_PAGE;

    $s = new SQLSelect();
    $s->AddTable("bbs_topics");
    $s->AddWhere("bbs_topics.id=".$this->id);
    $this->topic = SQLLib::SelectRow($s->GetQuery());
    if(!$this->topic)
    {
      return false;
    }

    $s = new SQLSelect();
    $s->AddField("count(*) as c");
    $s->AddTable("bbs_posts");
    $s->AddWhere("bbs_posts.topic=".$this->id);
    $this->postcount = SQLLib::SelectRow($s->GetQuery())->c;

    $s = new BM_Query();
    $s->AddTable("bbs_posts");
    $s->AddField("bbs_posts.id as id");
    $s->AddField("bbs_posts.post as post");
    $s->AddField("bbs_posts.added as added");
    $s->attach(array("bbs_posts"=>"author"),array("users as user"=>"id"));
    $s->AddWhere("bbs_posts.topic=".$this->id);
    //$s->SetLimit( $POSTS_PER_PAGE, (int)(($this->page - 1)*$POSTS_PER_PAGE) );

    $this->paginator = new PouetPaginator();
    $this->paginator->SetData( "topic.php?which=".$this->id, $this->postcount, $POSTS_PER_PAGE, @$_GET["page"] );
    $this->paginator->SetLimitOnQuery( $s );

    $this->posts = $s->perform();

    $this->title = _html($this->topic->topic);
  }

  function RenderBody()
  {
    global $POSTS_PER_PAGE;
    global $THREAD_CATEGORIES;
    global $currentUser;

    echo "<div class='threadcategory'>";
    echo "<b>category:</b> "._html($this->topic->category);
    if ($currentUser && $currentUser->CanEditBBS())
    {
      printf(" [<a href='admin_topic_edit.php?which=%d' class='adminlink'>edit</a>]\n",$this->id);
    }
    printf(" [<a href='gloperator_log.php?which=%d&amp;what=topic'>glöplog</a>]\n",$this->id);


    echo "</div>\n";

    if ($this->postcount > $POSTS_PER_PAGE)
    {
      echo $this->paginator->RenderNavbar();
    }
    else
    {
      echo "<div class='blank'>&nbsp;</div>\n";
    }

    foreach ($this->posts as $c)
    {
      $p = $c->post;
      $p = parse_message($p);
      echo "<div class='bbspost cite-".$c->user->id."' id='c".$c->id."'>\n";
      echo "  <div class='content'>".$p."</div>\n";
      echo "  <div class='foot'><span class='tools' data-cid='".$c->id."'></span> added on the <a href='topic.php?post=".$c->id."'>".$c->added."</a> by ".
        $c->user->PrintLinkedName()." ".$c->user->PrintLinkedAvatar()."</div>\n";
      echo "</div>\n\n";
    }

    if ($this->postcount > $POSTS_PER_PAGE)
    {
      echo $this->paginator->RenderNavbar();
    }
  }
  function RenderFooter()
  {
    echo "</div>\n";
  }
};

if (!@$_GET["which"])
{
  redirect("bbs.php");
}
$topicid = (int)$_GET["which"];
$view = new PouetBoxBBSView($topicid);
$view->Load();

$post = new PouetBoxBBSPost($topicid);

if ($view->topic)
{
  $TITLE = $view->topic->topic;
  $RSS["export/lastbbstopicposts.rss.php?topic=".(int)$view->topic->id] = "latest posts in ".$view->topic->topic;
}

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
if ($view->topic)
{
  echo $view->Render();
  if (!get_login_id())
  {
    require_once("include_pouet/box-login.php");
    $box = new PouetBoxLogin();
    $box->Render();
  }
  else
  {
    if ($view->topic->id == FIXMETHREAD_ID)
    {
      $msg = new PouetBoxModalMessage( true );
      $msg->uniqueID = "pouetbox_fixmewarning";
      $msg->classes[] = "errorbox";
      $msg->title = "want to add or update a link ?";
      $msg->message = "we've made a new automated edit request system to modify prods ands groups"
        . " - if you just want to add credits or links (including youtube or soundcloud links!) to prods,"
        . " <b>go to the prod page and click the edit link</b> ! same goes for <b>changing website urls for groups</b> !";
      $msg->Render();
?>
<style type="text/css">
#pouetbox_fixmewarning.warn {
  animation-duration: 0.5s;
  animation-name: blink;
  animation-iteration-count: infinite;
  animation-timing-function: ease;
  -webkit-animation-duration: 0.5s;
  -webkit-animation-name: blink;
  -webkit-animation-iteration-count: infinite;
  -webkit-animation-timing-function: ease;
}
@keyframes blink {
    0% { transform:scale(1); }
   50% { transform:scale(1.1); }
  100% { transform:scale(1); }
}
@-webkit-keyframes blink {
    0% { transform:scale(1); }
   50% { transform:scale(1.1); }
  100% { transform:scale(1); }
}
</style>
<script>
<!--
document.observe("dom:loaded",function(){
  var t = $$("textarea").first();
  t.observe("keyup",function(){
    if (t.value.indexOf("youtube.com")!=-1 || t.value.indexOf("youtu.be")!=-1)
    {
      $("pouetbox_fixmewarning").addClassName("warn");
    }
  });
});
//-->
</script>
<?php
    }
    if ($view->topic->closed)
    {
      $msg = new PouetBoxModalMessage( true );
      $msg->title = "thread closed";
      $msg->message = "this thread now officially wants YOU to go make a demo about it instead. please comply.";
      $msg->Render();
    }
    else if ($currentUser && !$currentUser->CanPostInBBS())
    {
      $msg = new PouetBoxModalMessage( true );
      $msg->title = "no you can't";
      $msg->message = "i think it's become painfully obvious that the pou&euml;t BBS is not for you.";
      $msg->Render();
    }
    else
    {
      echo $post->Render();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  $$(".tools").each(function(item){
    var cid = item.readAttribute("data-cid");
    item.update("<a href='#'>quote</a> |");
    item.down("a").observe("click",function(e){
      e.stop();
      new Ajax.Request("ajax_bbspost.php",{
        "method":"post",
        "parameters":$H({"id":cid}).toQueryString(),
        "onSuccess":function(transport){
          $("message").value += "[quote]" + transport.responseJSON.post.strip() + "[/quote]";
          try { $("message").scrollTo(); } catch(ex) {} // needs try-catch because of some dumbass popup blockers
        }
      });
    });
  });
  PreparePostForm( $$("#pouetbox_bbspost form").first() );
});
//-->
</script>
<?php
    }
  }
}
else
{
  echo "bla bla bla topic missing bla";
}
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
