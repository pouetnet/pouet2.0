<?
include_once("include_generic/sqllib.inc.php");
include_once("include_pouet/pouet-box.php");
include_once("include_pouet/pouet-prod.php");

class PouetBoxModalMessage extends PouetBox 
{
  function PouetBoxModalMessage( $allowHTML = false, $enableFooter = false ) 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_modalmessage";
    $this->title = "";
    $this->allowHTML = $allowHTML;
    $this->enableFooter = $enableFooter;
  }

  function RenderContent() {
    echo $this->allowHTML ? $this->message : _html( $this->message );
  }

  function RenderFooter() {
    if ($this->enableFooter)
      echo "  <div class='foot'><a href='".$_SERVER['HTTP_REFERER']."'>get back</a></div>\n";
    echo "</div>\n";
  }
};

?>