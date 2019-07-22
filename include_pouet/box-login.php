<?
require_once( POUET_ROOT_LOCAL . "/include_generic/sqllib.inc.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-box.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-prod.php");
require_once( POUET_ROOT_LOCAL . "/include_pouet/pouet-user.php");

class PouetBoxLogin extends PouetBox 
{
  var $data;
  var $prod;
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_login";
    $this->title = "login";
  }
  function RenderBody() 
  {
    echo "<div class='content loggedout'>\n";
    printf( "<a href='login.php?return=%s'>login via SceneID</a>",_html(rawurlencode(rootRelativePath())) );
    echo "</div>\n";
  }
};

?>