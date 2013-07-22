<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-bbs-post.php");

$POSTS_PER_PAGE = 25;

class PouetBoxOnelinerView extends PouetBox {
  function PouetBoxOnelinerView() {
    parent::__construct();
    $this->uniqueID = "pouetbox_onelinerview";
    $this->title = "the so complete pou&euml;t.net oneliner";
  }

  function LoadFromDB() {
    global $POSTS_PER_PAGE;
    
    $s = new SQLSelect();
    $s->AddField("count(*) as c");
    $s->AddTable("oneliner");
    $this->postcount = SQLLib::SelectRow($s->GetQuery())->c;

    $this->numpages = (int)ceil($this->postcount / $POSTS_PER_PAGE);
    if (!isset($_GET["page"]))
      $this->page = $this->numpages;
    else
      $this->page = (int)$_GET["page"];
      
    $this->page = (int)max( $this->page, 1 );
    $this->page = (int)min( $this->page, $this->numpages );
    
    $s = new BM_Query();
    $s->AddTable("oneliner");
    $s->AddField("oneliner.message");
    $s->attach(array("oneliner"=>"who"),array("users as user"=>"id"));    
    $s->SetLimit( $POSTS_PER_PAGE, (int)(($this->page - 1)*$POSTS_PER_PAGE) );
    $this->oneliner = $s->perform();
  }

  function RenderNavbar() {
    global $POSTS_PER_PAGE;
    echo "<div class='navbar'>\n";
    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='oneliner.php?page=".($this->page - 1)."'>previous page</a></div>\n";
    if ($this->page * $POSTS_PER_PAGE < $this->postcount)
      echo "  <div class='nextpage'><a href='oneliner.php?page=".($this->page + 1)."'>next page</a></div>\n";
    echo "  <div class='selector'>";
    echo "  <form action='oneliner.php' method='get'>\n";
    echo "   go to page <select name='page'>\n";
    
    $numpages = (int)ceil($this->postcount / $POSTS_PER_PAGE);
    for ($x = 1; $x <= $numpages; $x++)
      echo "      <option value='".$x."'".($x==$this->page?" selected='selected'":"").">".$x."</option>\n";
      
    echo "   </select> of ".$numpages."\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "  </form>\n";
    echo "  </div>\n";
    echo "</div>\n";
    return $s;
  }
  
  function RenderBody() {
    global $POSTS_PER_PAGE;

    echo "<ul class='boxlist'>";
    foreach ($this->oneliner as $c) {
      $p = $c->message;
      $p = _html($p);
      //$p = bbencode($p,true);
      $p = preg_replace("/([a-z]+:\/\/\S+)/","<a href='$1'>$1</a>",$p);
      $p = nl2br($p);
      $p = better_wordwrap($p,80," ");
      echo "<li>".$c->user->PrintLinkedAvatar()." ".$p."</li>\n";
    }
    echo "</ul>";

    if ($this->postcount > $POSTS_PER_PAGE) {
      echo $this->RenderNavbar();
    }
    ?>
    <script type="text/javascript">
    document.observe("dom:loaded",function(){ Youtubify($("pouetbox_onelinerview")); });
    </script>
    <?
  }
  function RenderFooter() {
    echo "</div>\n";
    return $s;
  }
};

$p = new PouetBoxOnelinerView();
$p->Load();

$TITLE = "oneliner";

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo $p->Render();
echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>