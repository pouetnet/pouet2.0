<?php
class PouetBoxIndexNews extends PouetBox
{
  var $data;
  var $prod;
  var $link;
  var $title;
  var $content;
  var $timestamp;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_newsbox";
    $this->title = "news box";
  }

  function Render()
  {
    echo "<div class='pouettbl ".$this->uniqueID."'>\n";
    echo " <h3><a href='".$this->link."'>"._html($this->title)."</a></h3>\n";
    echo " <div class='content'>\n".str_replace("<br>","<br/>",$this->content)."\n</div>\n";
    echo " <div class='foot'>lobstregated at <a href='http://www.bitfellas.org/'>BitFellas.org</a> on ".($this->timestamp)."</div>\n";
    echo "</div>\n";
  }
};

class PouetBoxIndexNewsBoxes extends PouetBoxCachable
{
  public $rss;
  public $limit;
  public $rssBitfellasNews;
  function __construct()
  {
    parent::__construct();

    $this->title = "news!";

    $this->cacheTime = 60*15;

    $this->uniqueID = "pouetbox_news";
    $this->rss = class_exists("DomDocument") ? new lastRSS(array(
      "cacheTime" => 5 * 60, // in seconds
      "dateFormat" => "Y-m-d",
      "stripHtml" => false,
    )) : null;

    $this->limit = 5;
  }

  function LoadFromDB()
  {
    $this->rssBitfellasNews = $this->rss ? $this->rss->get('http://www.bitfellas.org/e107_plugins/rss_menu/rss.php?1.2') : array();
  }

  function LoadFromCachedData($data)
  {
    $this->rssBitfellasNews = unserialize($data);
  }

  function GetCacheableData()
  {
    return serialize($this->rssBitfellasNews);
  }

  use PouetFrontPage;
  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
  }
  function GetParameterSettings()
  {
    return array(
      "limit" => array("name"=>"number of news items visible","default"=>5,"min"=>1,"max"=>10),
    );
  }

  function Render()
  {
    if (@!$this->rssBitfellasNews['items'])
    {
    	printf('Error: Unable to open BitFeed !');
    }
    else
    {
      $p = new PouetBoxIndexNews();
      for($i=0; $i < min(count($this->rssBitfellasNews['items']),$this->limit); $i++)
      {
        if (!$this->rssBitfellasNews['items'][$i]['title'])
          continue;
        $p->content = $this->rssBitfellasNews['items'][$i]['description'];
        $p->title = $this->rssBitfellasNews['items'][$i]['title'];
        $p->link = $this->rssBitfellasNews['items'][$i]['link'];
        $p->timestamp = $this->rssBitfellasNews['items'][$i]['pubDate'];
        $p->Render();
      }
    }
  }
};

$indexAvailableBoxes[] = "News";
?>
