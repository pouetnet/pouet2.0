<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-party-submit.php");

if ($currentUser && !$currentUser->CanEditItems())
{
  redirect("index.php");
  exit();
}

class PouetBoxAdminModificationRequests extends PouetBox
{
  function PouetBoxAdminModificationRequests( )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_adminreq";
    $this->title = "process the following requests";
  }
  function Commit($data)
  {
    global $currentUser;

    $req = SQLLib::SelectRow(sprintf_esc("select itemID,requestType,requestBlob,approved from modification_requests where id = %d",$data["requestID"]));
    if ($req->approved !== NULL)
      return array("this request was already processed");
      
    if ($data["requestDeny"])
    {
      $a = array();
      $a["gloperatorID"] = $currentUser->id;
      $a["approved"] = 0;
      $a["approveDate"] = date("Y-m-d H:i:s");
      SQLLib::UpdateRow("modification_requests",$a,"id=".(int)$data["requestID"]);
      return array();
    }
    
    $reqData = unserialize($req->requestBlob);
    global $REQUESTTYPES;
    if ($REQUESTTYPES[$req->requestType])
    {
      $errors = $REQUESTTYPES[$req->requestType]::Process($req->itemID,$reqData);
      if ($errors) return $errors;

      gloperator_log( $REQUESTTYPES[$req->requestType]::GetItemType(), $req->itemID, $req->requestType, $reqData );
    }
    else
    {
      return array("no such request type!");
    }

    $a = array();
    $a["gloperatorID"] = $currentUser->id;
    $a["approved"] = 1;
    $a["approveDate"] = date("Y-m-d H:i:s");
    SQLLib::UpdateRow("modification_requests",$a,"id=".(int)$data["requestID"]);

    return array();
  }
  function LoadFromDB()
  {
    $s = new BM_Query("modification_requests");
    $s->AddField("modification_requests.id");
    $s->AddField("modification_requests.requestType");
    $s->AddField("modification_requests.itemID");
    $s->AddField("modification_requests.itemType");
    $s->AddField("modification_requests.requestBlob");
    $s->AddField("modification_requests.requestDate");
    $s->Attach(array("modification_requests"=>"userID"),array("users as user"=>"id"));
    $s->Attach(array("modification_requests"=>"itemID"),array("prods as prod"=>"id"));
    $s->AddWhere("approved is null");
    $s->AddOrder("requestDate desc");
    $this->requests = $s->perform();
  }
  function Render()
  {
    global $REQUESTTYPES;
    echo "<table id='".$this->uniqueID."' class='boxtable'>\n";
    echo "  <tr>\n";
    echo "    <th colspan='6'>".$this->title."</th>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <th>date</th>\n";
    echo "    <th>user</th>\n";
    echo "    <th>item</th>\n";
    echo "    <th>request</th>\n";
    echo "    <th>details</th>\n";
    echo "    <th>&nbsp;</th>\n";
    echo "  </tr>\n";
    foreach($this->requests as $r)
    {
      echo "  <tr>\n";
      echo "    <td>".$r->requestDate."</td>\n";
      echo "    <td>".$r->user->PrintLinkedAvatar()." ".$r->user->PrintLinkedName()."</td>\n";
      echo "    <td>".$r->itemType.": ";
      switch ($r->itemType)
      {
        case "prod": if ($r->prod) echo $r->prod->RenderSingleRowShort();
      }
      echo "</td>\n";
      echo "    <td>".$REQUESTTYPES[$r->requestType]::Describe()."</td>\n";
      echo "    <td>";
      $data = unserialize($r->requestBlob);
      
      global $REQUESTTYPES;
      if ($REQUESTTYPES[$r->requestType])
        echo $REQUESTTYPES[$r->requestType]::Display($data);
      
      echo "</td>\n";
      echo "<td>";
      
      printf("<form action='%s' method='post' enctype='multipart/form-data'>\n",_html(selfPath()));
      $csrf = new CSRFProtect();
      $csrf->PrintToken();
      printf("  <input type='hidden' name='requestID' value='%d'/>",$r->id);
      printf("  <input type='submit' name='requestAccept' value='accept !'/>");
      printf("  <input type='submit' name='requestDeny' value='deny !'/>");
      printf("  <input type='hidden' name='%s' value='%s'/>\n",PouetFormProcessor::fieldName,"adminModReq");
      printf("</form>\n\n\n");
      
      echo "</td>\n";
      echo "  </tr>\n";
    }
    echo "</table>\n";
?>
<script type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  $$("#pouetbox_adminreq input[type='submit']").invoke("observe","click",function(e){ e.element().setAttribute("clicked","true"); });
  $$("#pouetbox_adminreq form").invoke("observe","submit",function(e){
    e.stop();
    e.element().select("input[type='submit']").invoke("setAttribute","disabled",true);
    var opt = Form.serializeElements( e.element().select("input[type='hidden']"), {hash:true} );
    opt["partial"] = true;
    opt[ e.element().select("input[type='submit'][clicked='true']").first().name ] = true;
    new Ajax.Request( e.element().action, {
      method: e.element().method,
      parameters: opt,
      onSuccess: function(transport) {
        if (transport.responseJSON.success)
        {
          e.element().up("tr").remove();
          fireSuccessOverlay( transport.responseJSON.success == "accepted" ? "request accepted !" : "request denied !");
        }
        else
        {
          fireErrorOverlay( transport.responseJSON.errors.join("<br/>") );
        }
      }
    });
  });
});
//-->
</script>
<?
  }
}


$form = new PouetFormProcessor();
$form->renderForm = false;

$box = new PouetBoxAdminModificationRequests( );
$form->Add( "adminModReq", $box );

if ($currentUser && $currentUser->CanEditItems())
{
  if ($_POST["partial"])
  {
    $form->SetSuccessURL( "", false );
    $form->Process();
    $response = array();
    if ($form->GetErrors())
    {
      $response["errors"] = $form->GetErrors();
    }
    else
    {
      $response["success"] = $_POST["requestAccept"] ? "accepted" : "denied";
    }
    header("Content-type: application/json; charset=utf-8");
    echo json_encode($response);
    exit();
  }
  else
  {
    $form->SetSuccessURL( "admin_modification_requests.php", true );
    $form->Process();
  }
}

$TITLE = "process modification requests";

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
