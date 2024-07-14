<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-party-submit.php");
require_once("include_pouet/pouet-box-editbase.php");

if ($currentUser && !$currentUser->IsModerator())
{
  redirect("index.php");
  exit();
}

class PouetBoxAdminAddCDC extends PouetBoxEditConnectionsBase
{
  public $data;
  public $allowDelete;
  public $headers;
  public static $slug = "CDC";
  function __construct( )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_admineditcdc";
    $this->title = "edit cdcs";
    $this->headers = array("prod","groups","added date");
    
    $s = new BM_Query();
    $s->AddTable("cdc");
    $s->AddField("cdc.id");
    $s->AddField("cdc.addedDate");
    $s->attach(array("cdc"=>"which"),array("prods as prod"=>"id"));
    $s->AddOrder("cdc.addedDate");
    $this->data = $s->perform();

    $a = array();
    foreach($this->data as $v) $a[] = &$v->prod;
    PouetCollectPlatforms($a);
  }
  use PouetForm;
  function Commit($data)
  {
    if (@$data["delAffil"])
    {
      SQLLib::Query("delete from cdc where id=".(int)$data["delCDC"]);
      return array();
    }

    $a = array();
    $a["which"] = $data["which"];
    $a["addedDate"] = $data["addedDate"];
    if (@$data["editCDCID"])
    {
      SQLLib::UpdateRow("cdc",$a,"id=".(int)$data["editCDCID"]);
      $a["id"] = $data["editCDCID"];
    }
    else
    {
      $a["id"] = SQLLib::InsertRow("cdc",$a);
    }
    
    @unlink(POUET_ROOT_LOCAL . 'cache/pouetbox_cdc.cache' );
    
    if (@$data["partial"])
    {
      $o = toObject($a);
      $o->prod = PouetProd::Spawn($a["which"]);
      $this->RenderNormalRow($o);
      $this->RenderNormalRowEnd($o);
      exit();
    }

    return array();
  }  
  function RenderEditRow($row = null)
  {
    echo "    <td colspan='2'><input name='which' value='"._html( $row ? $row->prod->id : "" )."' class='prodID'/></td>\n";
    echo "    <td><input name='addedDate' value='"._html( $row && $row->addedDate ? $row->addedDate : date("Y-m-d") )."'/></td>\n";
  }
  function RenderNormalRow($v)
  {
    echo "<td>\n";
    echo $v->prod->RenderTypeIcons();
    echo $v->prod->RenderPlatformIcons();
    echo "<span class='prod'>".$v->prod->RenderLink()."</span>\n";
    echo "</td>\n";

    echo "<td>\n";
    echo $v->prod->RenderGroupsShortProdlist();
    echo "</td>\n";

    echo "<td>\n";
    echo $v->addedDate;
    echo "</td>\n";
  }
  function RenderBody()
  {
    parent::RenderBody();
?>
<script>
<!--
document.observe("dom:loaded",function(){
  InstrumentAdminEditorForAjax( $("pouetbox_admineditcdc"), "prodAffil", {
    onRowLoad: function(tr){
      new Autocompleter(tr.down(".prodID"), {
        "dataUrl":"./ajax_prods.php",
        "processRow": function(item) {
          var s = item.name.escapeHTML();
          if (item.groupName) s += " <small class='group'>" + item.groupName.escapeHTML() + "</small>";
          return s;
        }
      });
    }
  } );
});
//-->
</script>
<?php
  }
}


$boxen = array(
  "PouetBoxAdminAddCDC",
);
if(@$_GET["partial"] && $currentUser && $currentUser->CanEditItems())
{
  // ajax responses
  $prod = new stdClass();
  $prod->id = $_GET["which"];
  foreach($boxen as $class)
  {
    $box = new $class( $prod );
    $box->RenderPartialResponse();
  }
  exit();
}

$form = new PouetFormProcessor();

$form->SetSuccessURL( "admin_cdc.php", true );

$form->Add( "prod", new PouetBoxAdminAddCDC() );
if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit faq";

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
