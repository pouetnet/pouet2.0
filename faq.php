<?
include_once("bootstrap.inc.php");

class PouetBoxFAQ extends PouetBox {
  function PouetBoxFAQ() {
    parent::__construct();
    $this->uniqueID = "pouetbox_faq";
    $this->title = "the always incomplete pouët.net faq";
  }

  function LoadFromDB() 
  {
    $this->entries = SQLLib::SelectRows("select * from faq order by category, id");
  }

  function RenderBody() 
  {
    echo "<div class='content'>\n";
    $lastType = "";
    foreach($this->entries as $e)
    {
      if ($lastType != $e->category)
      {
        if ($lastType)
          echo "</ul>\n";
        echo "<h3>"._html($e->category)."</h3>";
        $lastType = $e->category;
        echo "<ul>\n";
      }
      echo "<li><a href='#faq"._html($e->id)."'>".$e->question."</a></li>";
    }
    echo "</ul>\n";
    echo "</div>\n";

    $lastType = "";
    foreach($this->entries as $e)
    {
      if ($lastType != $e->category)
      {
        if ($lastType)
          echo "</dl>\n";
        echo "<h2>:: "._html($e->category)."</h2>";
        $lastType = $e->category;
        echo "<dl>\n";
      }
      echo "<dt id='faq"._html($e->id)."'>:: "._html($e->category)." :: ".$e->question."</dt>";
      echo "<dd>".$e->answer."</dd>";
    }
    echo "</dl>\n";
  }
};

$TITLE = "faq";

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box = new PouetBoxFAQ();
$box->Load();
$box->Render();

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");

?>