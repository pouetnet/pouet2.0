<?
include_once("include_generic/sqllib.inc.php");
include_once("include_pouet/pouet-box.php");
include_once("include_pouet/pouet-prod.php");

class PouetBoxSearchBox extends PouetBoxCachable {
  var $data;
  var $prod;
  function PouetBoxSearchBox() {
    parent::__construct();
    $this->uniqueID = "pouetbox_search";
    $this->title = "search box";
  }

  function RenderBody() {
    echo "<form action='search.php' method='get'>\n";
    echo "<div class='content center'>\n";
    echo "<input type='text' name='what' size='25'/>\n";
    echo "</div>\n";
    echo "<div class='content center buttons'>\n";

    $types = array("prod","group","party"/*,"board"*/,"user","bbs");
    $a = array();
    foreach($types as $t)
      $a[] = "<input type='radio' name='type' value='".$t."' id='search".$t."' ".($t=="prod"?" checked='checked'":"")." />&nbsp;<label for='search".$t."'>".$t."</label>\n";

    echo implode("\n",$a);
    /*
    echo "<input type='radio' name='type' value='prod' id='prod' checked='checked' />&nbsp;<label for='prod'>prod</label>\n";
    echo "<input type='radio' name='type' value='group' id='group'/>&nbsp;<label for='group'>group</label>\n";
    echo "<input type='radio' name='type' value='party' id='party'/>&nbsp;<label for='party'>party</label>\n";
    echo "<input type='radio' name='type' value='board' id='board'/>&nbsp;<label for='board'>board</label>\n";
    echo "<input type='radio' name='type' value='user' id='user'/>&nbsp;<label for='user'>user</label>\n";
    echo "<input type='radio' name='type' value='bbs' id='bbs'/>&nbsp;<label for='bbs'>bbs</label>\n";
    */
    echo "</div>\n";
    echo "<div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</form>\n";
  }

};

?>