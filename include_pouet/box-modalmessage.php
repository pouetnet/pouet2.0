<?php
require_once(POUET_ROOT_LOCAL . "/include_generic/sqllib.inc.php");
require_once(POUET_ROOT_LOCAL . "/include_pouet/pouet-box.php");
require_once(POUET_ROOT_LOCAL . "/include_pouet/pouet-prod.php");

class PouetBoxModalMessage extends PouetBox
{
  public $allowHTML;
  public $enableFooter;
  public $returnPage;
  public $message;
  function __construct( $allowHTML = false, $enableFooter = false )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_modalmessage";
    $this->title = "";
    $this->allowHTML = $allowHTML;
    $this->enableFooter = $enableFooter;
    $this->returnPage = @$_SERVER['HTTP_REFERER'];
  }

  function RenderContent() {
    echo $this->allowHTML ? $this->message : _html( $this->message );
  }

  function RenderFooter() {
    if ($this->enableFooter)
      echo "  <div class='foot'><a href='"._html($this->returnPage)."'>get back</a></div>\n";
    echo "</div>\n";
  }
};

?>
