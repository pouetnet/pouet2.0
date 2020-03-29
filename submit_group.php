<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-group-submit.php");

if ($currentUser && !$currentUser->CanSubmitItems())
{
  redirect("index.php");
  exit();
}

$TITLE = "submit a group";

$form = new PouetFormProcessor();

$form->SetSuccessURL( "groups.php?which={%NEWID%}", true );

$form->Add( "group", new PouetBoxSubmitGroup() );

if ($currentUser && $currentUser->CanSubmitItems())
  $form->Process();

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (get_login_id())
{
  $form->Display();

?>
<script>
document.observe("dom:loaded",function(){
  NameWarning({"ajaxURL":"./ajax_groups.php","linkURL":"groups.php?which="});
});
</script>
<?php

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
