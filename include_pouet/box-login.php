<?php
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
    $loginURL = "login.php?return=".rawurlencode(rootRelativePath());

    echo "<div class='content loggedout'>\n";
    printf( "<a href='%s'>login via SceneID</a>",_html($loginURL) );
    echo "</div>\n";

    echo "<div class='foot'>\n";
    echo "<a href='"._html($loginURL)."'>log in</a> ::\n";
    echo "<a href='https://id.scene.org/register/'>register</a>\n";
    echo "</div>";
  }
};

?>