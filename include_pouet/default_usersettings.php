<?

//$r = SQLLib::SelectRow("select * from usersettings where id = 11057");
//echo serialize($r);

$TEST = array(
   'indextopglops' => 10,
   'indextopprods' => 10,
   'indexoneliner' => 5,
   'indexlatestadded' => 5,
   'indexlatestreleased' => 5,
   'indexojnews' => 5,
   'indexlatestcomments' => 5,
   'indexbbstopics' => 10,
   'topicposts' => 25,
   'bbsbbstopics' => 25,
   'prodlistprods' => 25,
   'searchprods' => 25,
   'userlogos' => 10,
   'userprods' => 10,
   'usergroups' => 10,
   'userparties' => 10,
   'userscreenshots' => 10,
   'usernfos' => 10,
   'usercomments' => 10,
   'userrulez' => 10,
   'usersucks' => 10,
   'commentshours' => 24,
   'indexcdc' => 1,
   'indexsearch' => 1,
   'indexlinks' => 1,
   'indexstats' => 1,
   'logos' => 1,
   'topbar' => 1,
   'bottombar' => 1,
   'userlistusers' => 25,
   'topichidefakeuser' => 0,
   'prodhidefakeuser' => 0,
   'indextype' => 1,
   'indexplatform' => 1,
   'indexwhoaddedprods' => 0,
   'indexwhocommentedprods' => 0,
   'indexlatestparties' => 5,
   'indextopkeops' => 10,
   'displayimages' => 1,
   'indexbbsnoresidue' => 1,
   'prodcomments' => -1,
   'indexwatchlist' => 5,
   'customizerJSON' => '{"frontpage":{"leftbar":[{"box":"Login"},{"box":"CDC","limit":"1"},{"box":"LatestAdded","limit":"5"},{"box":"LatestReleased","limit":"5"},{"box":"TopMonth","limit":"10"},{"box":"TopAlltime","limit":"10"}],"middlebar":[{"box":"LatestOneliner","limit":"5"},{"box":"LatestBBS","limit":"10"},{"box":"NewsBoxes","limit":"5"}],"rightbar":[{"box":"SearchBox","limit":"1"},{"box":"Stats","limit":"1"},{"box":"AffilButton","limit":"1"},{"box":"LatestComments","limit":"5"},{"box":"Watchlist","limit":"5"},{"box":"LatestParties","limit":"5"},{"box":"UpcomingParties"},{"box":"TopGlops","limit":"10"}]}}',
);
$DEFAULT_USERSETTINGS = new stdClass();
foreach($TEST as $k=>$v) $DEFAULT_USERSETTINGS->$k = $v;

?>
