<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

class PouetBoxSubmitLogo extends PouetBox
{
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_submitlogo";
    $this->title = "submit a logo!";
  }
  use PouetForm;
  function Validate( $data )
  {
    global $currentUser;

    $errormessage = array();

    if (!$currentUser)
      return array("you have to be logged in !");

    if (!$currentUser->CanSubmitItems())
      return array("not allowed lol !");

    if (!is_uploaded_file($_FILES["logo"]["tmp_name"]))
      return array("upload error !");

    list($width,$height,$type) = getimagesize( $_FILES["logo"]["tmp_name"] );

    if($width > 700)
      $errormessage[]="the width can't be bigger than 700 pixels";

    if($height > 200)
      $errormessage[]="the height can't be bigger than 200 pixels";

    if($type != IMAGETYPE_GIF && $type != IMAGETYPE_PNG && $type != IMAGETYPE_JPEG)
      $errormessage[]="the file must be a .gif, .png or .jpg file";

    if(filesize( $_FILES["logo"]["tmp_name"] ) > 128 * 1024)
      $errormessage[]="the file is too big ! 128 kilobytes should be enough for everybody !";

    $filename = strtolower( basename( $_FILES["logo"]["name"] ) );

    if(!preg_match("/^[a-z0-9_-]{1,32}\.(gif|jpg|jpeg|png)$/",$filename))
      $errormessage[]="please give a senseful filename devoid of dumb characters, kthx? (nothing but alphanumerics, dash and underscore is allowed, 32 chars max)";

    if(file_exists(POUET_CONTENT_LOCAL . "/logos/".$filename))
      $errormessage[]="this filename already exists on the server";

    if (count($errormessage))
      return $errormessage;

    return array();
  }
  function Commit($data)
  {
    global $currentUser;

    $filename = strtolower( basename( $_FILES["logo"]["name"] ) );

    move_uploaded_file_fake( $_FILES["logo"]["tmp_name"], POUET_CONTENT_LOCAL . "/logos/".$filename );

    $a = array();
    $a["author1"] = $currentUser->id;
    $a["author2"] = NULL;
    $a["file"] = $filename;
    SQLLib::InsertRow("logos",$a);

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

    echo "<p><b>.gif</b> <b>.jpg</b> or <b>.png</b> width and height limit are <b>700</b>x<b>200</b></p>\n";
		echo "<p>Its size must be < <b>128KB</b>.</p>\n";
    echo "<p>Don't forget to optimize your logo to fit well against the <a href='".POUET_CONTENT_URL."gfx/trumpet.gif'>pouet background</a>\n";
		echo "(by using transparency), or it will look like a noob picture.</p>\n";
    echo "<p>The pou&euml;t background color is <b>#3A6EA5</b>.</p>\n";
		echo "<p>Before being displayed, your logo will be voted up or down by the whole Pouet community.</p>\n";
		echo "<p>Don't blame us for not displaying it if it's lame, the scene is rude, and that's why we like it !</p>\n";
    echo "<input type='file' name='logo' accept='image/*'/>";

    echo "  </div>\n";
    echo "  <div class='foot'><input name='submit' type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};

$form = new PouetFormProcessor();

$form->SetSuccessURL( "", false );

$form->Add( "logo", new PouetBoxSubmitLogo() );

if ($currentUser && $currentUser->CanSubmitItems())
  $form->Process();

$TITLE = "submit a logo";

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
