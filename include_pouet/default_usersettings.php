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
   'commentsnamecut' => 50,
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
   'indexbbsnoresidue' => 0,
   'prodcomments' => -1,
);
$DEFAULT_USERSETTINGS = new stdClass();
foreach($TEST as $k=>$v) $DEFAULT_USERSETTINGS->$k = $v;
?>