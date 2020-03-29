<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-prod-submit.php");

if ($currentUser && !$currentUser->CanSubmitItems())
{
  redirect("index.php");
  exit();
}

$TITLE = "submit a prod";

$form = new PouetFormProcessor();

$form->SetSuccessURL( "prod.php?which={%NEWID%}", true );

$form->Add( "prod", new PouetBoxSubmitProd() );

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
  if (!$("row_csdbID")) return;
  PrepareSubmitForm();
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
