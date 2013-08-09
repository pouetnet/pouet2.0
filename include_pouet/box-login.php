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
      echo "<form action='login.php' method='post'>\n";
      
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
      /*
?>
<script type="text/javascript">
var loginClicked = false;
function loginResetFields()
{
  if (!loginClicked)
  {
    $("loginusername").value = "";
    $("loginpassword").value = "";
  }
  loginClicked = true;
}
$("loginusername").observe("focus",loginResetFields);
$("loginpassword").observe("focus",loginResetFields);
</script>
<?
    */
    } else {
      global $currentUser;
      echo "<div class='content r1 center'>\n";
      echo "you are logged in as<br/>\n";
      echo "<a href='user.php?who=".$currentUser->id."'><img src='".POUET_CONTENT_URL."avatars/"._html($currentUser->avatar)."' alt='"._html($currentUser->nickname)."'></a>\n";
      echo "<a href='user.php?who=".$currentUser->id."'><b>"._html($currentUser->nickname)."</b></a>\n";
      echo "</div>\n";
      echo "<div class='foot'>\n";
      echo "<a href='account.php'>account</a> ::\n";
      //echo "<a href='account2.php'>custom</a> |\n";
      echo "<a href='logout.php'>logout</a>\n";
      echo "</div>";
    }
  }

};

?>
