<?
include_once("include_generic/sqllib.inc.php");
include_once("include_pouet/pouet-box.php");
include_once("include_pouet/pouet-prod.php");

class PouetBoxNews extends PouetBoxCachable {
  var $data;
  var $prod;
  var $link;
  var $title;
  var $content;
  var $timestamp;
  function PouetBoxNews() {
    parent::__construct();
    $this->uniqueID = "pouetbox_news";
    $this->title = "news box";
  }

  function Render() {
    echo "<div class='pouettbl ".$this->uniqueID."'>\n";
    echo " <h3><a href='".$this->link."'>"._html($this->title)."</a></h3>\n";
    echo " <div class='content'>\n".str_replace("<br>","<br/>",$this->content)."\n</div>\n";
    echo " <div class='foot'>lobstregated at <a href='http://www.bitfellas.org/'>BitFellas.org</a> on ".($this->timestamp)."</div>\n";
    echo "</div>\n";
  }

};

?>