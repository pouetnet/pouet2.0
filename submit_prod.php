<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");
include_once("include_pouet/box-prod-submit.php");

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

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (get_login_id())
{
  $form->Display();

?>
<script type="text/javascript">
document.observe("dom:loaded",function(){
  if (!$("row_csdbID")) return;
  PrepareSubmitForm();
});
</script>
<?

}
else
{
  include_once("include_pouet/box-login.php");
  $box = new PouetBoxLogin();
  $box->Render();
}

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");

?>
