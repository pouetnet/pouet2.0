<?php
require_once("bootstrap.inc.php");

class PouetBoxPreview extends PouetBox {
  function __construct() {
    parent::__construct();
    $this->uniqueID = "pouetbox_preview";
    $this->title = "this is what marcellus wallace looks like";
  }

  function RenderContent()
  {
    echo parse_message( substr( $_POST["message"], 0, 65535 ) );
  }
};

$TITLE = "post preview";

require_once("include_pouet/header.bare.php");

echo "<div id='content'>\n";

$box = new PouetBoxPreview();
$box->Render();

echo "</div>\n";

require_once("include_pouet/footer.bare.php");
?>
