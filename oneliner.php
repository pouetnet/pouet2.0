<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-bbs-post.php");

$POSTS_PER_PAGE = 25;

class PouetBoxOnelinerView extends PouetBox 
{
  public $postcount;
  public $paginator;
  public $oneliner;
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_onelinerview";
    $this->title = "the so complete pou&euml;t.net oneliner";
  }

  function LoadFromDB() {
    global $POSTS_PER_PAGE;

    $s = new SQLSelect();
    $s->AddField("count(*) as c");
    $s->AddTable("oneliner");
    if (@$_GET["who"])
      $s->AddWhere(sprintf_esc("oneliner.who = %d",$_GET["who"]));
    $this->postcount = SQLLib::SelectRow($s->GetQuery())->c;

    $s = new BM_Query();
    $s->AddTable("oneliner");
    $s->AddField("oneliner.message");
    $s->AddField("oneliner.addedDate");
    $s->attach(array("oneliner"=>"who"),array("users as user"=>"id"));
    if (@$_GET["who"])
      $s->AddWhere(sprintf_esc("oneliner.who = %d",$_GET["who"]));
    //$s->SetLimit( $POSTS_PER_PAGE, (int)(($this->page - 1)*$POSTS_PER_PAGE) );

    $this->paginator = new PouetPaginator();
    $this->paginator->SetData( (@$_GET["who"] ? "oneliner.php?who=".(int)$_GET["who"] : "oneliner.php"), $this->postcount, $POSTS_PER_PAGE, @$_GET["page"] );
    $this->paginator->SetLimitOnQuery( $s );

    $this->oneliner = $s->perform();
  }

  function RenderBody() 
  {
    global $POSTS_PER_PAGE;

    echo "<ul class='boxlist'>";
    $lastDate = "";
    foreach ($this->oneliner as $c) 
    {
      $day = substr($c->addedDate,0,10);
      if ($day != $lastDate)
      {
        echo "<li class='day'>".$day."</li>\n";
        $lastDate = $day;
      }
      $p = $c->message;
      $p = _html($p);
      //$p = bbencode($p,true);
      $p = preg_replace("/([a-z]+:\/\/\S+)/","<a href='$1' rel='external'>$1</a>",$p);
      $p = nl2br($p);
      $p = better_wordwrap($p,80," ");
      echo "<li>";
      echo "<time datetime='".$c->addedDate."' title='".$c->addedDate."'>".date("H:i",strtotime($c->addedDate))."</time> ";
      echo $c->user->PrintLinkedAvatar()." ".$p;
      echo "</li>\n";
    }
    echo "</ul>";

    $this->paginator->RenderNavbar();
   ?>
    <script>
    document.observe("dom:loaded",function(){ StubLinksToDomainName($("pouetbox_onelinerview")); });
    </script>
    <?php        
  }
  function RenderFooter() 
  {
    echo "</div>\n";
  }
};

$p = new PouetBoxOnelinerView();
$p->Load();

$TITLE = "oneliner";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo $p->Render();
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
