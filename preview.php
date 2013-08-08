<?
require_once("bootstrap.inc.php");

class PouetBoxPreview extends PouetBox {
  function PouetBoxPreview() {
    parent::__construct();
    $this->uniqueID = "pouetbox_preview";
    $this->title = "this is what marcellus wallace looks like";
  }

  function RenderContent() 
  {
    echo parse_message( $_POST["message"] );
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
