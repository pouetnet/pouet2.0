<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

class PouetBoxAvatarGallery extends PouetBox
{
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_avatargallery";
    $this->title = "some of the currently available avatars";
  }
  use PouetForm;
  function Render()
  {
    global $currentUser;
    if (!$currentUser)
      return;

    if (!$currentUser->CanSubmitItems())
      return;

    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";

    echo "  <h2>".$this->title."</h2>\n";
    echo "  <div class='content'>\n";

    $width = 15;

    $g = glob(POUET_CONTENT_LOCAL."avatars/*.gif");
    shuffle($g);
    $g = array_slice($g,0,$width * $width);

    echo "<ul id='avatargallery'>\n";
    foreach($g as $v)
      printf("  <li><img src='".POUET_CONTENT_URL."avatars/%s' alt='%s' title='%s'/></li>\n",basename($v),basename($v),basename($v));
    echo "</ul>\n";

    echo "  </div>\n";
    echo "</div>\n";
  }
};
class PouetBoxSubmitAvatar extends PouetBox
{
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_submitavatar";
    $this->title = "submit an avatar!";
  }
  use PouetForm;
  function Validate( $data )
  {
    global $groupID,$currentUser;

    $errormessage = array();

    if (!$currentUser)
      return array("you have to be logged in !");

    if (!$currentUser->CanSubmitItems())
      return array("not allowed lol !");

    if (!is_uploaded_file($_FILES["avatar"]["tmp_name"]))
      return array("upload error !");

    list($width,$height,$type) = getimagesize( $_FILES["avatar"]["tmp_name"] );

    if($width != 16)
      $errormessage[]="the width must be equal to 16 pixels";

    if($height != 16)
      $errormessage[]="the height must be equal to 16 pixels";

    if($type != IMAGETYPE_GIF)
      $errormessage[]="the file must be a .gif file";

    if(filesize( $_FILES["avatar"]["tmp_name"] ) > 4096)
      $errormessage[]="the file is too big ! 4096 bytes should be enough for everybody !";

    $filename = strtolower( basename( $_FILES["avatar"]["name"] ) );

    if(!preg_match("/^[a-z0-9_-]{1,32}\.gif$/",$filename))
      $errormessage[]="please give a senseful filename devoid of dumb characters, kthx? (nothing but alphanumerics, dash and underscore is allowed, 32 chars max)";

    if(file_exists(POUET_CONTENT_LOCAL."avatars/".$filename))
      $errormessage[]="this filename already exists on the server";

    if (count($errormessage))
      return $errormessage;

    return array();
  }
  function Commit($data)
  {
    $filename = strtolower( basename( $_FILES["avatar"]["name"] ) );

    move_uploaded_file_fake( $_FILES["avatar"]["tmp_name"], POUET_CONTENT_LOCAL . "avatars/".$filename );

    return array();
  }

  function Render()
  {
    global $currentUser;
    if (!$currentUser)
      return;

    if (!$currentUser->CanSubmitItems())
      return;

    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";

    echo "  <h2>".$this->title."</h2>\n";
    echo "  <div class='content'>\n";

    //echo "<div id='avatarexample'><img src='".POUET_CONTENT_URL."gfx/example.gif'/></div>\n";
    echo "in order to upload a new avatar, make sure to follow those rules:<br>";
    echo "<ul>";
    echo "<li>it's a <b>.gif</b> file</li>";
    echo "<li>it's <b>16</b> pixel width</li>";
    echo "<li>it's <b>16</b> pixel height</li>";
    echo "<li>it's <b>4</b>Kb max.</li>";
    echo "<li>the background color is set to <b>transparent</b></li>";
    echo "</ul>";
    echo "if everything's ok, you can upload your file by using the <b>browse</b> button below.<br>";
    echo "<br>";
    echo "then your avatar will be available with the others.<br>";
    echo "<br>";
    echo "<input type='file' name='avatar' accept='image/gif'/>";

    echo "  </div>\n";
    echo "  <div class='foot'><input name='submit' type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};

$form = new PouetFormProcessor();

$form->SetSuccessURL( "", false );

$form->Add( "avatarGallery", new PouetBoxAvatarGallery() );
$form->Add( "avatar", new PouetBoxSubmitAvatar() );

if ($currentUser && $currentUser->CanSubmitItems())
  $form->Process();

$TITLE = "submit an avatar";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (get_login_id())
{
  $form->Display();
}
else
{
  require_once("include_pouet/box-login.php");
  $box = new PouetBoxLogin();
  $box->Render();
}

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
