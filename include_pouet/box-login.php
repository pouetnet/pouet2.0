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
  function SetParameters($data)
  {
  }
  function RenderBody() {
    if (!get_login_id())
    {
      echo "<form action='".POUET_ROOT_PATH."login.php' method='post'>\n";

      $csrf = new CSRFProtect();
      $csrf->PrintToken();

      echo "<div class='content r1 center'>\n";
      //echo "<input id='loginusername' type='text' name='login' value='SceneID' size='15' maxlength='16'/><br />\n";
      //echo "<input id='loginpassword' type='password' name='password' value='password' size='15'/><br />\n";
      echo "<input id='loginusername' type='text'     name='login'    size='15' placeholder='SceneID'/><br />\n";
      echo "<input id='loginpassword' type='password' name='password' size='15' placeholder='password'/><br />\n";
//      echo "<input type='checkbox' name='permanent'/>login for 1 year<br />\n";
      echo "<input type='hidden' name='return' value='"._html(rootRelativePath())."'/>\n";
      echo "<a href='account.php'>register here</a>\n";
      echo "</div>\n";
      echo "<div class='foot'><input type='submit' value='Submit'/></div>";
      echo "</form>\n";
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
      echo "<a href='logout.php'>logout</a>\n";
      echo "</div>";
    }
  }

};

?>
