<?
class PouetBoxLatestAdded extends PouetBoxCachable {
  var $data;
  var $prods;
  function PouetBoxLatestAdded() {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestadded";
    $this->title = "latest added prods";
  }

  function LoadFromCachedData($data) {
    $this->data = unserialize($data);
  }

  function GetData()
  {
    return $this->data;
  }

  function GetCacheableData() {
    return serialize($this->data);
  }

  function LoadFromDB() {
    $s = new BM_Query("prods");
    $s->AddOrder("prods.quand desc");
    $s->attach("added",array("users as user"=>"id"));
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();
    PouetCollectPlatforms($this->data);
  }
  
  function RenderBody() {
    echo "<ul class='boxlist boxlisttable'>\n";
    $n = 0;
    foreach($this->data as $p) 
    {
      echo "<li>\n";
      echo "<span class='rowprod'>\n";
      echo $p->RenderAsEntry();
      echo "</span>\n";
      if (get_setting("indexwhoaddedprods"))
      {
        echo "<span class='rowuser'>\n";
        echo $p->user->PrintLinkedAvatar();
        echo "</span>\n";
      }
      echo "</li>\n";
      if (++$n == get_setting("indexlatestadded")) break;
    }
    echo "</ul>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='prodlist.php?order=added'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

?>