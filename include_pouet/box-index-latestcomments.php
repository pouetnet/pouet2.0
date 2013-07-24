<?
include_once("include_generic/sqllib.inc.php");
include_once("include_pouet/pouet-box.php");
include_once("include_pouet/pouet-prod.php");

class PouetBoxLatestComments extends PouetBoxCachable {
  var $data;
  var $prods;
  function PouetBoxLatestComments() {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestcomments";
    $this->title = "latest comments added";
  }

  function LoadFromCachedData($data) {
    $this->data = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->data);
  }

  function LoadFromDB() {
    $s = new BM_Query();
    $s->AddTable("(select * from comments order by comments.quand desc limit 25) as c");
    $s->attach(array("c"=>"which"),array("prods as prod"=>"id"));
    $s->attach(array("c"=>"who"),array("users as user"=>"id"));
    $s->AddOrder("c.quand desc");
    $s->AddField("c.id as commentID");
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();
    
    $a = array();
    foreach($this->data as $p) $a[] = &$p->prod;
    PouetCollectPlatforms($a);
  }
 
  function RenderBody() {
    echo "<ul class='boxlist boxlisttable'>\n";
    $n = 0;
    foreach($this->data as $d) 
    {
      echo "<li>\n";
      echo "<span class='rowprod'>\n";
      echo $d->prod->RenderAsEntry();
      echo "</span>\n";
      if (get_setting("indexwhocommentedprods"))
      {
        echo "<span class='rowuser'>\n";
        echo $d->user->PrintLinkedAvatar();
        echo "</span>\n";
      }
      echo "</li>\n";
      if (++$n == get_setting("indexlatestcomments")) break;
    }
    echo "</ul>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='comments.php'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

?>
