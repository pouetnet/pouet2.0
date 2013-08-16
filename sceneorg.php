<?
require_once("bootstrap.inc.php");

class PouetBoxSceneOrgAwards extends PouetBox {
  function PouetBoxSceneOrgAwards() {
    parent::__construct();
    $this->uniqueID = "pouetbox_sceneorgawards";
    $this->title = "scene.org awards";
  }

  function LoadFromDB()
  {
    $s = new BM_Query("sceneorgrecommended");
    $s->AddField("sceneorgrecommended.type");
    $s->AddField("sceneorgrecommended.category");
    $s->attach(array("sceneorgrecommended"=>"prodID"),array("prods as prod"=>"id"));
    $s->AddOrder("date_format(sceneorgrecommended_prod.date,'%Y') DESC");
    $s->AddOrder("sceneorgrecommended.category");
    $s->AddOrder("sceneorgrecommended.type");
    $s->AddWhere("sceneorgrecommended.type != 'viewingtip'");
    $this->sceneorg = $s->perform();

    $a = array();
    foreach($this->sceneorg as $v) $a[] = &$v->prod;
    PouetCollectPlatforms($a);
  }

  function RenderBody()
  {
    echo "\n\n";
    echo "<table class='boxtable'>\n";
    $lastYear = 0;
    $lastCategory = "";
    foreach ($this->sceneorg as $row)
    {
      if ($lastYear != substr($row->prod->date,0,4))
      {
        $lastYear = substr($row->prod->date,0,4);
        echo "<tr><th colspan='3' class='year'>".$lastYear."</th></tr>\n";
      }
      if ($lastCategory != $row->category)
      {
        $lastCategory = $row->category;
        echo "<tr id='".str_replace(" ","",$lastYear.$lastCategory)."'><th colspan='3' class='category'>".$lastCategory."</th></tr>\n";
      }
      $p = $row->prod;
      echo "<tr>\n";
      echo "<td>\n";
      echo "<img src='".POUET_CONTENT_URL."gfx/sceneorg/".$row->type.".gif' alt='".$row->type."'/>&nbsp;";
      echo $p->RenderTypeIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderPlatformIcons();
      echo "</td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};


class PouetBoxSceneOrgTips extends PouetBox {
  function PouetBoxSceneOrgTips() {
    parent::__construct();
    $this->uniqueID = "pouetbox_sceneorgtips";
    $this->title = "scene.org viewing tips";
  }

  function LoadFromDB()
  {
    $s = new BM_Query("sceneorgrecommended");
    $s->AddField("sceneorgrecommended.type");
    $s->attach(array("sceneorgrecommended"=>"prodID"),array("prods as prod"=>"id"));
    $s->AddOrder("date_format(sceneorgrecommended_prod.date,'%Y') DESC");
    $s->AddWhere("sceneorgrecommended.type = 'viewingtip'");
    $this->sceneorg = $s->perform();

    $a = array();
    foreach($this->sceneorg as $v) $a[] = &$v->prod;
    PouetCollectPlatforms($a);
  }

  function RenderBody()
  {
    echo "\n\n";
    echo "<table class='boxtable'>\n";
    $lastYear = 0;
    $lastCategory = "";
    foreach ($this->sceneorg as $row)
    {
      if ($lastYear != substr($row->prod->date,0,4))
      {
        $lastYear = substr($row->prod->date,0,4);
        echo "<tr id='".$lastYear."'><th colspan='3' class='year'>".$lastYear."</th></tr>\n";
      }
      $p = $row->prod;
      echo "<tr>\n";
      echo "<td>\n";
      echo $p->RenderTypeIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderPlatformIcons();
      echo "</td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
};

$TITLE = "scene.org awards and viewing tips";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box = new PouetBoxSceneOrgAwards();
$box->Load();
$box->Render();

$box = new PouetBoxSceneOrgTips();
$box->Load();
$box->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
