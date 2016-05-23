<?
header("Content-type: text/html; charset=utf-8");
$RSS["export/lastprodsreleased.rss.php"] = "last prods released";
$RSS["export/lastprodsadded.rss.php"] = "last prods added";
$RSS["export/lastbbsposts.rss.php"] = "last bbs posts";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?=_html($TITLE?$TITLE." :: pouët.net":"pouët.net :: your online demoscene resource")?></title>

  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
  <link rel="search" type="application/opensearchdescription+xml" href="opensearch_prod.xml" title="pouët.net: prod search" />
  <link rel="canonical" href="http://www.pouet.net<?=_html($_SERVER["REQUEST_URI"])?>"/>
<?foreach($RSS as $url=>$title){?>
  <link rel="alternate" href="<?=_html($url)?>" type="application/rss+xml" title="pouët.net: <?=_html($title)?>">
<?}?>

  <link rel="stylesheet" type="text/css" href="<?=POUET_CONTENT_URL?>styles/001/style.css?<?=filemtime(POUET_CONTENT_LOCAL."styles/001/style.css")?>" media="screen" />
  <?if ( POUET_MOBILE ) {?>
  <link rel="stylesheet" href="<?=POUET_CONTENT_URL?>styles/001/mobile.css?<?=filemtime(POUET_CONTENT_LOCAL."styles/001/mobile.css")?>" type="text/css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0;" />
  <?}?>
  
<?
  if ($metaValues) foreach ($metaValues as $k=>$v)
  {
    printf("  <meta property=\"%s\" content=\"%s\"/>\n",$k,_html($v));
  }
?>  

  <script type="text/javascript">
  <!--
    var pixelWidth = screen.width;
    var Pouet = {};
    Pouet.isMobile = <?=POUET_MOBILE?"true":"false"?>;
  //-->
  </script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.1.0/prototype.js"></script>
  <script type="text/javascript" src="./jsonp.js"></script>
  <script type="text/javascript" src="./cookie.js"></script>
  <script type="text/javascript" src="./autocompleter.js"></script>
  <script type="text/javascript" src="./script.js?<?=filemtime("script.js")?>"></script>

  <!--[if lt IE 9]><script src="//ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->
  <!--[if IE]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="description" content="pouët.net - your online demoscene resource"/>
  <meta name="keywords" content="pouët.net,pouët,pouet.net,pouet,256b,1k,4k,40k,64k,cracktro,demo,dentro,diskmag,intro,invitation,lobster sex,musicdisk,Amiga AGA,Amiga ECS,Amiga PPC,Amstrad CPC,Atari ST,BeOS,Commodore 64,Falcon,MS-Dos,Linux,MacOS,Windows"/>
</head>
<body>
