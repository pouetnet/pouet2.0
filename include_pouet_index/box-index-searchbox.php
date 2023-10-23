<?php
class PouetBoxIndexSearchBox extends PouetBoxCachable
{
  var $data;
  var $prod;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_search";
    $this->title = "search box";
  }

  function RenderBody()
  {
    echo "<form action='search.php' method='get'>\n";
    echo "<div class='content center'>\n";
    echo "<input type='text' name='what' size='25'/>\n";
    echo "</div>\n";
    echo "<div class='content center buttons'>\n";

    $types = array("prod","group","party"/*,"board"*/,"user","bbs");
    $a = array();
    foreach($types as $t)
      $a[] = "<li><label><input type='radio' name='type' value='".$t."' ".($t=="prod"?" checked='checked'":"")."/>&nbsp;".$t."</label></li>\n";

    echo "<ul id='searchType'>";
    echo implode("\n",$a);
    echo "</ul>";
    /*
    echo "<label><input type='radio' name='type' value='prod' checked='checked'/>&nbsp;prod</label>\n";
    echo "<label><input type='radio' name='type' value='group'/>&nbsp;group</label>\n";
    echo "<label><input type='radio' name='type' value='party'/>&nbsp;party</label>\n";
    echo "<label><input type='radio' name='type' value='board'/>&nbsp;board</label>\n";
    echo "<label><input type='radio' name='type' value='user'/>&nbsp;user</label>\n";
    echo "<label><input type='radio' name='type' value='bbs'/>&nbsp;bbs</label>\n";
    */
    echo "</div>\n";
    echo "<div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</form>\n";
  }

};

$indexAvailableBoxes[] = "SearchBox";
?>