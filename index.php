<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-login.php");
include_once("include_pouet/box-index-bbs-latest.php");
include_once("include_pouet/box-index-cdc.php");
include_once("include_pouet/box-index-latestadded.php");
include_once("include_pouet/box-index-latestreleased.php");
include_once("include_pouet/box-index-latestcomments.php");
include_once("include_pouet/box-index-latestparties.php");
include_once("include_pouet/box-index-upcomingparties.php");
include_once("include_pouet/box-index-topmonth.php");
include_once("include_pouet/box-index-topalltime.php");
include_once("include_pouet/box-index-news.php");
include_once("include_pouet/box-index-searchbox.php");
include_once("include_pouet/box-index-affilbutton.php");
include_once("include_pouet/box-index-stats.php");
include_once("include_pouet/box-index-user-topglops.php");
include_once("include_pouet/box-index-oneliner-latest.php");

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

// setup transparent cache
$rss = new lastRSS(); 
$rss->cache_dir = './cache'; 
$rss->cache_time = 5*60; // in seconds
$rss->CDATA = 'strip'; 
$rss->date_format = 'Y-m-d'; 
$rss->itemtags[] = "demopartynet:title";
$rss->itemtags[] = "demopartynet:startDate";
$rss->itemtags[] = "demopartynet:endDate";

$rssBitfellasNews = $rss->get('http://bitfellas.org/e107_plugins/rss_menu/rss.php?1.2');
$rssDemopartyUpcoming = $rss->get('http://feeds.demoparty.net/demoparty/parties');

echo "<div id='content' class='frontpage'>\n";
echo "  <div id='leftbar'>\n";

$st = microtime_float();

$p = new PouetBoxLogin();
$p->Render();

if (get_setting("indexcdc"))
{
  $p = new PouetBoxCDC();
  $p->Load(true);
  $p->Render();
}
  
if (get_setting("indexlatestadded"))
{
  $p = new PouetBoxLatestAdded();
  $p->Load(true);
  $p->Render();
}
  
if (get_setting("indexlatestreleased"))
{
  $p = new PouetBoxLatestReleased();
  $p->Load(true);
  $p->Render();
}
  
if (get_setting("indextopprods"))
{
  $p = new PouetBoxTopMonth();
  $p->Load(true);
  $p->Render();
}
  
if (get_setting("indextopkeops"))
{
  $p = new PouetBoxTopAlltime();
  $p->Load(true);
  $p->Render();
}

printf("<!-- presentation of left bar took %f-->\n",microtime_float() - $st);

echo "  </div>\n";


echo "  <div id='middlebar'>\n";

if (get_setting("indexoneliner"))
{
  $p = new PouetBoxLatestOneliner();
  $p->Load(true);
  $p->Render();
}

if (get_setting("indexbbstopics"))
{
  $p = new PouetBoxLatestBBS();
  $p->Load(true);
  $p->Render();
}


if (!$rssBitfellasNews) {
	printf('Error: Unable to open BitFeed!');
} else {
  $p = new PouetBoxNews();
  for($i=0; $i < get_setting("indexojnews"); $i++) 
  {
    $p->content = $rssBitfellasNews['items'][$i]['description'];
    $p->title = $rssBitfellasNews['items'][$i]['title'];
    $p->link = $rssBitfellasNews['items'][$i]['link'];
    $p->timestamp = $rssBitfellasNews['items'][$i]['pubDate'];
    $p->Render();
  }
}

echo "  </div>\n";

echo "  <div id='rightbar'>\n";

if (get_setting("indexsearch"))
{
  $p = new PouetBoxSearchBox();
  $p->Render();
}

if (get_setting("indexstats"))
{
  $p = new PouetBoxStats();
  $p->Load(true);
  $p->Render();
}

if (get_setting("indexlinks"))
{
  $p = new PouetBoxAffilButton();
  $p->Load();
  $p->Render();
}

if (get_setting("indexlatestcomments"))
{
  $p = new PouetBoxLatestComments();
  $p->Load(true);
  $p->Render();
}

if (get_setting("indexlatestparties"))
{
  $p = new PouetBoxLatestParties();
  $p->Load(true);
  $p->Render();
}

$p = new PouetBoxUpcomingParties( $rssDemopartyUpcoming );
$p->Load(true);
$p->Render();

if (get_setting("indextopglops"))
{
  $p = new PouetBoxTopGlops();
  $p->Load(true);
  $p->Render();
}

echo "  </div>\n";
echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>
