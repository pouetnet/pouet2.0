<?php
require_once("bootstrap.inc.php");

class PouetBoxFAQ extends PouetBox
{
  public $entries;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_faq";
    $this->title = "the always incomplete pouët.net faq";
  }

  function LoadFromDB()
  {
    $this->entries = SQLLib::SelectRows("select * from faq where deprecated = 0 order by category, id");
  }

  function RenderBody()
  {
    echo "<div class='content' id='faq_toc'>\n";
    $lastType = "";
    foreach($this->entries as $e)
    {
      if ($lastType != $e->category)
      {
        if ($lastType)
          echo "</ul>\n";
        echo "<h3>"._html($e->category)."</h3>\n";
        $lastType = $e->category;
        echo "<ul>\n";
      }
      echo "<li><a href='#faq"._html($e->id)."'>".$e->question."</a></li>\n";
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
        echo "<h2>:: "._html($e->category)."</h2>\n";
        $lastType = $e->category;
        echo "<dl class='faq'>\n";
      }
      echo "<dt id='faq"._html($e->id)."'>:: "._html($e->category)." :: ".$e->question."</dt>\n";
      echo "<dd>".$e->answer."</dd>\n";
    }
    echo "</dl>\n";
  }
};

$TITLE = "faq";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box = new PouetBoxFAQ();
$box->Load();
$box->Render();

echo "</div>\n";

?>
<script>
<!--
document.observe("dom:loaded",function(){
  $("faq_toc").hide();
  $$(".faq > dd").invoke("hide");
  $$(".faq > dt").each(function(item){
    item.update( "[<a href='#" + item.id + "'>#</a>] " + item.innerHTML );
    item.setStyle({"cursor":"pointer"});
    item.observe("click",function(ev){
      ev.findElement("dt").nextSiblings().first().toggle();
      if (!ev.findElement("a"))
        ev.stop();
    });
  });

  var e = $$("dt#" + location.hash);
  if (e.length) e.first().nextSiblings().first().show();
  var v = location.hash; location.hash = v; // force firefox
});
//-->
</script>
<?php

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
