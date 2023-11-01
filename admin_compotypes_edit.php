<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-group-submit.php");
require_once("include_pouet/pouet-box-editbase.php");

if ($currentUser && !$currentUser->IsModerator())
{
  redirect("index.php");
  exit();
}

class PouetBoxCompotypesEditBox extends PouetBoxEditConnectionsBase
{
  public $allowDelete;
  public $headers;
  public $data;
  public static $slug = "Compo";
  function __construct( )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_compotypesedit";
    $this->title = "edit compo types";
    $this->allowDelete = false; // for now

    $this->headers = array("#","compo name");
    $this->data = SQLLib::SelectRows("select * from compotypes order by id");
  }
  use PouetForm;
  function Commit($data)
  {
    /*
    if ($data["delLink"])
    {
      SQLLib::Query("delete from downloadlinks where id=".(int)$data["delLink"]);
      gloperator_log( "prod", (int)$this->prod->id, "prod_link_del" );
      return array();
    }
    */
    
    $a = array();
    $a["componame"] = trim($data["componame"]);
    if (@$data["editCompoID"])
    {
      SQLLib::UpdateRow("compotypes",$a,"id=".(int)$data["editCompoID"]);
      $a["id"] = $data["editCompoID"];
    }
    else
    {
      $a["id"] = SQLLib::InsertRow("compotypes",$a);
    }
    @unlink( POUET_ROOT_LOCAL . "/cache/enum-compotypes.cache" );
    if (@$data["partial"])
    {
      $this->RenderNormalRow(toObject($a));
      $this->RenderNormalRowEnd(toObject($a));
      exit();
    }
    return array();
  }
  function RenderEditRow($row = null)
  {
    echo "    <td>"._html($row?$row->id:"")."</td>\n";
    echo "    <td><input name='componame' value='"._html($row?$row->componame:"")."'/></td>\n";
  }
  function RenderNormalRow($row)
  {
    echo "    <td>"._html($row->id)."</td>\n";
    echo "    <td>"._html($row->componame)."</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_compotypesedit"), "prodLink" );
});
//-->
</script>
<?php
  }
};

$boxen = array(
  "PouetBoxCompotypesEditBox",
);
if(@$_GET["partial"] && $currentUser && $currentUser->IsModerator())
{
  // ajax responses
  foreach($boxen as $class)
  {
    $box = new $class();
    $box->RenderPartialResponse();
  }
  exit();
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "admin_compotypes_edit.php", true );

$form->Add( "compotypesedit", new PouetBoxCompotypesEditBox() );

if ($currentUser && $currentUser->IsModerator())
  $form->Process();

$TITLE = "edit compo types";

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
