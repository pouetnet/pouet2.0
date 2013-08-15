<?
$timer["html"]["start"] = microtime_float();
header("Content-type: text/html; charset=utf-8");
require_once("header.bare.php");
?>

<header>
  <h1>pouët.net</h1>
<?
if (get_setting("logos"))
{
  $timer["logo"]["start"] = microtime_float();
  $s = new BM_Query();
  $s->AddTable("logos");
  $s->AddField("logos.file as file");
  $s->attach(array("logos"=>"author1"),array("users as u1"=>"id"));
  $s->attach(array("logos"=>"author2"),array("users as u2"=>"id"));
  $s->AddOrder("rand()");
  $s->AddWhere("logos.vote_count>0");
  $s->SetLimit("1");
  list($logo) = $s->perform();

  if ($logo)
  {
    $credit = $logo->u1->PrintLinkedName();
    if ($logo->u2)
      $credit .= " and " . $logo->u2->PrintLinkedName();
  }
  $timer["logo"]["end"] = microtime_float();


  $random_quotes = Array (
    'send your logos to <a href="submit_logo.php">us</a> and be a popstar !',
    '<a href="logo_vote.php">vote</a> for the logos you like and be a lamah !',
    'pouët.net is brought to you by <a href="http://www.pouet.net/groups.php?which=5">mandarine</a>',
    'pouët.net is hosted on the huge <a href="http://www.scene.org/">scene.org</a> servers',
  /*
    'pou?t != scene && scene != pou?t',
    'help make KOOL DEMO-SHOCK to japanese brain',
    'i am not an atomic playboy',
    'glop me beautiful'
  */
    );
?>
  <a href="./index.php"><img src="<?=POUET_CONTENT_URL?>gfx/logos/<?=$logo->file?>" alt="logo"/></a>
  <p>logo done by <?=$credit?> :: <?=$random_quotes[ array_rand($random_quotes) ]?></p>
<?
} else {
?>
  <a href="./index.php">pouët.net</a>
<?
}
?>
</header>
