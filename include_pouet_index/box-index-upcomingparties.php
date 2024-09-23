<?php
class PouetBoxIndexUpcomingParties extends PouetBoxCachable
{
  public $rss;
  public $limit;
  public $rssData;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_upcomingparties";
    $this->title = "upcoming parties";
    $this->cacheTime = 60*60*6; // update every 6 hours (4 times a day should be reasonable)

    $this->rss = class_exists("DomDocument") ? new lastRSS(array(
      "cacheTime" => 5 * 60, // in seconds
      "dateFormat" => "Y-m-d",
      "stripHtml" => false,
    )) : null;
    if ($this->rss)
    {
      $this->rss->setItemTags(array(
        "link",
        "demopartynet:title",
        "demopartynet:startDate",
        "demopartynet:endDate",
      ));
    }

    $this->limit = 5;
  }

  use PouetFrontPage;
  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
  }
  function GetParameterSettings()
  {
    return array(
      "limit" => array("name"=>"number of parties visible","default"=>5,"min"=>1,"max"=>10),
    );
  }

  function LoadFromCachedData($data)
  {
    $this->rssData = unserialize($data);
  }

  function GetCacheableData()
  {
    return serialize($this->rssData);
  }

  function LoadFromDB()
  {
    $this->rssData = $this->rss ? $this->rss->get('https://www.demoparty.net/demoparties.xml') : array();
  }

  function RenderBody()
  {
    if (@!$this->rssData['items'])
    {
      return;
    }
    echo "<ul class='boxlist'>\n";
    for($i=0; $i < min( count($this->rssData['items']),$this->limit); $i++)
    {
    	$st = strtotime($this->rssData['items'][$i]['demopartynet:startDate']);
    	$et = strtotime($this->rssData['items'][$i]['demopartynet:endDate']);
    	$sd = strtolower( date("M j",$st) );
    	$ed = strtolower( date("M j",$et) );
    	$form = "";
    	if ($sd == $ed)
    	  $form = $sd;
    	else if (substr($sd,0,3)==substr($ed,0,3))
    	  $form = $sd . " - " . substr($ed,4);
    	else
    	  $form = $sd . " - " . $ed;
    	$dist = (int)ceil( ($st - time()) / 60 / 60 / 24 );

      echo "<li>\n";
      echo "<a href='".$this->rssData['items'][$i]['link']."'>".$this->rssData['items'][$i]['demopartynet:title']."</a> ";
      echo " <span class='timeleft'>";
      echo $form;
      if ($dist <= 0) echo " (now!)";
      else if ($dist == 1) echo " (tomorrow)";
      else echo " (".$dist." days)";
      echo "</span>";
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
  function RenderFooter()
  {
    echo "  <div class='foot'><a href='http://www.demoparty.net/'>more at demoparty.net</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "UpcomingParties";
?>
