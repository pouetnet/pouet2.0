<?
if ($_GET["forceDesktop"]==1) $_COOKIE["noMobile"] = 1;
if ($_GET["enableMobile"]==1) $_COOKIE["noMobile"] = 0;

header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?=_html($TITLE?$TITLE." :: pouët.net":"pouët.net :: your online demoscene resource")?></title>

  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
  <link rel="search" type="application/opensearchdescription+xml" href="opensearch_prod.xml" title="pouët.net: prod search" />
  <link rel="canonical" href="http://www.pouet.net<?=_html($_SERVER["REQUEST_URI"])?>"/>
  <link rel="alternate" href="export/lastprodsreleased.rss.php" type="application/rss+xml" title="pouët.net: last prods released">
  <link rel="alternate" href="export/lastprodsadded.rss.php" type="application/rss+xml" title="pouët.net: last prods added">
  <link rel="alternate" href="export/lastbbsposts.rss.php" type="application/rss+xml" title="pouët.net: last bbs posts">

  <link rel="stylesheet" type="text/css" href="<?=POUET_CONTENT_URL?>styles/001/style.css?<?=filemtime(POUET_CONTENT_LOCAL."styles/001/style.css")?>" media="screen" />
  <?if (!$_COOKIE["noMobile"] && (POUET_TEST || ($currentUser && $currentUser->IsAdministrator()))) {?>
  <link rel="stylesheet" media="only screen and (max-device-width: 480px) and (min-device-width: 320px)" href="<?=POUET_CONTENT_URL?>styles/001/mobile.css?<?=filemtime(POUET_CONTENT_LOCAL."styles/001/mobile.css")?>" type="text/css" />
  <meta name="viewport" content="width=device-width; initial-scale=1.0;" />
  <?}?>

  <script type="text/javascript">
  <!--
    var pixelWidth = screen.width * (window.devicePixelRatio ? window.devicePixelRatio : 1);
    var Pouet = {};
    Pouet.isMobile = <?=$_COOKIE["noMobile"]?"false":"true"?> && (pixelWidth <= 480);
  //-->
  </script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.1.0/prototype.js"></script>
  <script type="text/javascript" src="./jsonp.js"></script>
  <script type="text/javascript" src="./autocompleter.js"></script>
  <script type="text/javascript" src="./script.js"></script>
  

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="description" content="pouët.net - your online demoscene resource"/>
  <meta name="keywords" content="pouët.net,pouët,pouet.net,pouet,256b,1k,4k,40k,64k,cracktro,demo,dentro,diskmag,intro,invitation,lobster sex,musicdisk,Amiga AGA,Amiga ECS,Amiga PPC,Amstrad CPC,Atari ST,BeOS,Commodore 64,Falcon,MS-Dos,Linux,MacOS,Windows"/>
</head>
<body>
