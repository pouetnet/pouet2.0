<?php
require_once("bootstrap.inc.php");

class PouetBoxCDCModerator extends PouetBox
{
  public $cdcs;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_cdcmoderator";
    $this->title = "moderators' coup de coeur history";
  }

  function LoadFromDB()
  {
    $s = new BM_Query();
    $s->AddTable("cdc");
    $s->AddField("cdc.addedDate");
    $s->attach(array("cdc"=>"which"),array("prods as prod"=>"id"));
    $s->AddOrder("cdc.addedDate desc");
    $this->cdcs = $s->perform();

    $a = array();
    foreach($this->cdcs as $v) $a[] = &$v->prod;
    PouetCollectPlatforms($a);
  }

  function RenderBody()
  {
    echo "\n\n";
    echo "<table class='boxtable'>\n";
    foreach ($this->cdcs as $row)
    {
      $p = $row->prod;
      echo "<tr>\n";
      echo "<td>\n";
      echo $p->RenderTypeIcons();
      echo $p->RenderPlatformIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      echo $row->addedDate;
      echo "</td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};

class PouetBoxCDCUser extends PouetBox
{
  public $cdcs;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_cdcuser";
    $this->title = "users' coup de coeur toplist";
  }

  function LoadFromDB()
  {
    $s = new BM_Query();
    $s->AddTable("users_cdcs");
    $s->attach(array("users_cdcs"=>"cdc"),array("prods as prod"=>"id"));
    $s->AddGroup("users_cdcs.cdc");
    $s->AddField("count(*) as c");
    $s->AddOrder("c desc");
    $this->cdcs = $s->perform();

    $a = array();
    foreach($this->cdcs as $v) $a[] = &$v->prod;
    PouetCollectPlatforms($a);
  }

  function RenderBody()
  {
    echo "\n\n";
    echo "<table class='boxtable'>\n";
    $lastYear = 0;
    $lastCategory = "";
    foreach ($this->cdcs as $row)
    {
      if (!$row->prod) continue;
      $p = $row->prod;
      echo "<tr>\n";
      echo "<td>\n";
      echo $p->RenderTypeIcons();
      echo $p->RenderPlatformIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      echo $row->c;
      echo "</td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};


$TITLE = "coup de coeur";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box = new PouetBoxCDCModerator();
$box->Load();
$box->Render();

$box = new PouetBoxCDCUser();
$box->Load();
$box->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
