<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");
require_once("include_pouet/pouet-user.php");

class PouetBoxLogin extends PouetBox {
  var $data;
  var $prod;
  function PouetBoxLogin() {
    parent::__construct();
    $this->uniqueID = "pouetbox_login";
    $this->title = "login";
  }
  function RenderBody() {
    if (!get_login_id())
    {
      echo "<div class='content loggedout'>\n";
      printf( "<a href='login.php?return=%s'>login via SceneID</a>",_html(rootRelativePath()) );
      echo "</div>\n";
    } else {
      global $currentUser;
      echo "<div class='content loggedin'>\n";
      echo "you are logged in as<br/>\n";
      echo $currentUser->PrintLinkedAvatar()." ";
      echo $currentUser->PrintLinkedName();
      echo "</div>\n";
      if ($currentUser->IsGloperator())
      {
        $req = SQLLib::SelectRow("select count(*) as c from modification_requests where approved is null")->c;
        if ($req)
        {
          echo "<div class='content notifications'>\n";
          echo "[ <a href='admin_modification_requests.php' class='adminlink'>";
          echo $req;
          if ($req==1)
            echo " request waiting!";
          else
            echo " requests waiting!";
          echo "</a> ]";
          echo "</div>\n";
        }
      }
      echo "<div class='foot'>\n";
      echo "<a href='account.php'>account</a> ::\n";
      echo "<a href='customizer.php'>cust&ouml;omizer</a> ::\n";
      echo "<a href='logout.php'>logout</a>\n";
      echo "</div>";
    }
  }

};

?>