<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");
include_once("include_pouet/box-party-submit.php");

if ($currentUser && !$currentUser->CanSubmitItems())
{
  redirect("index.php");
  exit();
}

$TITLE = "submit a party";

$form = new PouetFormProcessor();

$form->SetSuccessURL( "party.php?which={%NEWID%}", true );

$form->Add( "party", new PouetBoxSubmitParty() );

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
  NameWarning({"ajaxURL":"./ajax_parties.php","linkURL":"party.php?which="});  
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