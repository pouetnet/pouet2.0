<?php
require_once( POUET_ROOT_LOCAL . "/include_pouet/box-login.php");

class PouetBoxIndexLogin extends PouetBoxLogin
{
  function RenderBody()
  {
    if (!get_login_id())
    {
      parent::RenderBody();
      return;
    }
    
    global $currentUser;
    if (!$currentUser)
    {
      LOG::Warning("Login ID is ".get_login_id()." but currentUser is null!");
      parent::RenderBody();
      return;
    }
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
    
    $broken = SQLLib::SelectRow(sprintf_esc(
      " select count(*) as c from prods_linkcheck".
      " left join prods on prods.id = prods_linkcheck.prodID".
      " where prods.addedUser = %d and (returnCode = 0 or (returnCode >= 400 && returnCode <= 599))",$currentUser->id));
    if ($broken->c)
    {
      echo "<div class='content notifications'>\n";
      printf("[ <a href='http://cardboard.pouet.net/broken_links.php?userID=me'>you have %d broken links !</a> ]",$broken->c);
      echo "</div>\n";
    }
    
    $cheevs = array(
         64 => "you're definitely keeping up with the commodore !",
        286 => "you just entered protected mode !",
        808 => "so many glöps - it's quite a kick !",
       1337 => "above this level is elitez only !",
       6510 => "you've clearly been processing a lot !",
      31337 => "greetings to elitez only !",
      64738 => "maybe it's time to reset your account !",
    );
    if (@$cheevs[$currentUser->glops])
    {
      echo "<div class='content achievements'>\n";
      printf("<p><b>congratulations ! you reached %d glöps !</b></p>",$currentUser->glops);
      printf("<p>%s</p>",$cheevs[$currentUser->glops]);
      echo "</div>\n";
    }
   
    
    echo "<div class='foot'>\n";
    echo "<a href='account.php'>edit profile</a> ::\n";
    echo "<a href='customizer.php'>cust&ouml;omizer</a> ::\n";
    echo "<a href='logout.php'>logout</a>\n";
    echo "</div>";
  }
};

$indexAvailableBoxes[] = "Login";
?>