<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");

class PouetBoxNews extends PouetBox {
  var $data;
  var $prod;
  var $link;
  var $title;
  var $content;
  var $timestamp;
  function PouetBoxNews() {
    parent::__construct();
    $this->uniqueID = "pouetbox_newsbox";
    $this->title = "news box";
  }

  function Render() {
    echo "<div class='pouettbl ".$this->uniqueID."'>\n";
    echo " <h3><a href='".$this->link."'>"._html($this->title)."</a></h3>\n";
    echo " <div class='content'>\n".str_replace("<br>","<br/>",$this->content)."\n</div>\n";
    echo " <div class='foot'>lobstregated at <a href='http://www.bitfellas.org/'>BitFellas.org</a> on ".($this->timestamp)."</div>\n";
    echo "</div>\n";
  }
};

class PouetBoxNewsBoxes extends PouetBoxCachable
{
  function PouetBoxNewsBoxes()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_news";
    $this->rss = new lastRSS();
    $this->rss->cache_dir = './cache';
    $this->rss->cache_time = 5*60; // in seconds
    $this->rss->CDATA = 'strip';
    $this->rss->date_format = 'Y-m-d';

    $this->limit = 5;
  }

  function LoadFromDB()
  {
    $this->rssBitfellasNews = $this->rss->get('http://bitfellas.org/e107_plugins/rss_menu/rss.php?1.2');
  }

  function LoadFromCachedData($data) {
    $this->rssBitfellasNews = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->rssBitfellasNews);
  }

  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
  }

  function Render()
  {
    if (!$this->rssBitfellasNews) {
    	printf('Error: Unable to open BitFeed!');
    } else {
      $p = new PouetBoxNews();
      for($i=0; $i < $this->limit; $i++)
      {
        $p->content = $this->rssBitfellasNews['items'][$i]['description'];
        $p->title = $this->rssBitfellasNews['items'][$i]['title'];
        $p->link = $this->rssBitfellasNews['items'][$i]['link'];
        $p->timestamp = $this->rssBitfellasNews['items'][$i]['pubDate'];
        $p->Render();
      }
    }
  }
};

?>
