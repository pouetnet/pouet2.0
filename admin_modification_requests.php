<?php
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
  public $requests;
  function __construct( )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_adminreq";
    $this->title = "process the following requests";
  }
  use PouetForm;
  function Commit($data)
  {
    global $currentUser;

    $req = SQLLib::SelectRow(sprintf_esc("select itemID,requestType,requestBlob,approved from modification_requests where id = %d",$data["requestID"]));
    if ($req->approved !== NULL)
      return array("this request was already processed");
      
    if (@$data["requestDeny"])
    {
      $a = array();
      $a["gloperatorID"] = $currentUser->id;
      $a["approved"] = 0;
      $a["comment"] = $data["comment"];
      $a["approveDate"] = date("Y-m-d H:i:s");
      SQLLib::UpdateRow("modification_requests",$a,"id=".(int)$data["requestID"]);
      return array();
    }
    
    $reqData = unserialize($req->requestBlob);
    global $REQUESTTYPES;
    if ($REQUESTTYPES[$req->requestType])
    {
      $errors = null;
      try
      {
        $errors = $REQUESTTYPES[$req->requestType]::Process($req->itemID,$reqData);
      }
      catch(Exception $e)
      {
        $errors = array((string)$e);
      }
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
    $s = new BM_Query();
    $s->AddTable("modification_requests");
    $s->AddField("modification_requests.id");
    $s->AddField("modification_requests.requestType");
    $s->AddField("modification_requests.itemID");
    $s->AddField("modification_requests.itemType");
    $s->AddField("modification_requests.requestBlob");
    $s->AddField("modification_requests.requestDate");
    $s->Attach(array("modification_requests"=>"userID"),array("users as user"=>"id"));
    $s->Attach(array("modification_requests"=>"itemID"),array("prods as prod"=>"id")  ,"modification_requests.itemType = 'prod'");
    $s->Attach(array("modification_requests"=>"itemID"),array("groups as group"=>"id"),"modification_requests.itemType = 'group'");
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
        case "prod" : if ($r->prod ) echo $r->prod->RenderSingleRowShort(); break;
        case "group": if ($r->group) echo $r->group->RenderLong(); break;
      }
      echo "</td>\n";
      echo "    <td>".$REQUESTTYPES[$r->requestType]::Describe()."</td>\n";
      echo "    <td>";
      $data = unserialize($r->requestBlob);
      
      global $REQUESTTYPES;
      if ($REQUESTTYPES[$r->requestType])
        echo $REQUESTTYPES[$r->requestType]::Display($r->itemID,$data);
      
      echo "</td>\n";
      echo "<td>";
      
      printf("<form action='%s' method='post' enctype='multipart/form-data'>\n",_html(selfPath()));
      $csrf = new CSRFProtect();
      $csrf->PrintToken();
      printf("  <input type='hidden' name='requestID' value='%d'/>",$r->id);
      printf("  <input type='submit' name='requestAccept' value='accept !'/>");
      printf("  <input type='submit' name='requestDeny' value='deny !'/>");
      printf("  <span class='result'></span>");
      printf("  <input type='hidden' name='%s' value='%s'/>\n",PouetFormProcessor::fieldName,"adminModReq");
      printf("</form>\n\n\n");
      
      echo "</td>\n";
      echo "  </tr>\n";
    }
    echo "</table>\n";
?>
<script>
<!--
document.observe("dom:loaded",function(){
<?php 
if (defined("YOUTUBE_FRONTEND_KEY")) {
?>
  function Youtubify( parentElement, detailed )
  {
    var videoIDs = {};
    var playlistIDs = {};
    var ytAPIKey = "<?=YOUTUBE_FRONTEND_KEY?>";
    parentElement.select("a[rel='external']").each(function(element){
      var videoID = element.href.match(/youtu(\.be\/|.*v=)([a-zA-Z0-9_\-]{11})/);
      if (videoID)
      {
        videoIDs[videoID[2]] = element;
        return;
      }
      var playlistID = element.href.match(/youtube\.com\/playlist\?.*list=([a-zA-Z0-9_\-]{34})/);
      if (playlistID)
      {
        playlistIDs[playlistID[1]] = element;
        return;
      }

      var pouetID = element.href.match(/pouet\.net\/prod\.php.*which=([0-9]+)/);
      if (pouetID)
      {
        new Ajax.JSONRequest("https://api.pouet.net/v1/prod/?id="+pouetID[1],{
          method: "get",
          onSuccess: function(transport) {
            if (transport.responseJSON.success)
            {
              var s = transport.responseJSON.prod.name;
              item.update( s.escapeHTML() );
              item.addClassName("pouet");
            }
          },
        });
        return;
      }      
      var demozooProdID = element.href.match(/demozoo\.org\/productions\/([0-9]+)/);
      if (demozooProdID)
      {
        new Ajax.JSONRequest("https://demozoo.org/api/v1/productions/"+demozooProdID[1]+"/?format=jsonp",{
          method: "get",
          onSuccess: function(transport) {
            if (transport.responseJSON)
            {
              var s = transport.responseJSON.title;
              item.update( s.escapeHTML() );
              item.addClassName("demozoo");
            }
          },
        });
        return;
      }

    });
    if (Object.keys(videoIDs).length)
    {
      new Ajax.JSONRequest("https://www.googleapis.com/youtube/v3/videos?id=" + Object.keys(videoIDs).join(",") + "&key=" + ytAPIKey + "&part=snippet",{
        method: "get",
        onException: function(r, ex) { throw ex; },
        onSuccess: function(transport) {
          if (transport.responseJSON.items)
          {
            for(var i=0; i<transport.responseJSON.items.length; i++)
            {
              var item = transport.responseJSON.items[i];
              var element  = videoIDs[item.id];
              var s = item.snippet.title.escapeHTML();
              if (detailed)
              {
                s += " <small>("+item.snippet.channelTitle.escapeHTML()+")</small>";
              }
              element.update( s );
              element.addClassName("youtube");
            }
          }
        },
      });
    }
    if (Object.keys(playlistIDs).length)
    {
      new Ajax.JSONRequest("https://www.googleapis.com/youtube/v3/playlists?id=" + Object.keys(playlistIDs).join(",") + "&key=" + ytAPIKey + "&part=snippet",{
        method: "get",
        onException: function(r, ex) { throw ex; },
        onSuccess: function(transport) {
          if (transport.responseJSON.items)
          {
            for(var i=0; i<transport.responseJSON.items.length; i++)
            {
              var item = transport.responseJSON.items[i];
              var element  = playlistIDs[item.id];
              var s = item.snippet.title.escapeHTML();
              if (detailed)
              {
                s += " <small>("+item.snippet.channelTitle.escapeHTML()+")</small>";
              }
              element.update( s );
              element.addClassName("youtube");
            }
          }
        },
      });
    }
  }

  $$("#pouetbox_adminreq th[colspan]").first().insert( " [" );
  $$("#pouetbox_adminreq th[colspan]").first().insert( new Element("a",{"href":"#"}).update("resolve youtube links").observe("click",function(){
    Youtubify( $("pouetbox_adminreq").down('tbody'), true );
  }) );
  $$("#pouetbox_adminreq th[colspan]").first().insert( "]" );
<?php 
}
?>  
  function fireError( e, msg )
  {
    console.error( "[pouet] There's been an error:\n--------------\n" + msg );
    fireErrorOverlay( "There's been an error:<br/>" + msg );
    
    e.element().select("input[type='submit']").invoke("removeAttribute","disabled");
    
    var result = e.element().select(".result").first();
    result.update("&#x26A0;");
    result.setAttribute("title","There's been an internal error; check console for details.");
  }
  
  $$("#pouetbox_adminreq input[type='submit']").invoke("observe","click",function(e){ e.element().setAttribute("clicked","true"); });
  $$("#pouetbox_adminreq form").invoke("observe","submit",function(e){
    e.stop();
    
    var reqAction = e.element().select("input[type='submit'][clicked='true']").first().name;
    var reason = null;
    if (reqAction == "requestDeny")
    {
      reason = prompt("Enter the reason why you want to deny this request");
      if (reason == null || !reason.length)
        return;
    }
    e.element().select("input[type='submit']").invoke("setAttribute","disabled",true);
    var opt = Form.serializeElements( e.element().select("input[type='hidden']"), {hash:true} );
    opt["partial"] = true;
    opt["comment"] = reason;
    opt[ reqAction ] = true;
    new Ajax.Request( e.element().action, {
      method: e.element().method,
      parameters: opt,
      onException: function(r, ex) { fireError( e, ex ); throw ex; },
      onSuccess: function(transport) 
      {
        if (!transport.responseJSON || !transport.responseJSON.success)
        {
          var msg = (transport.responseJSON && transport.responseJSON.errors) ? transport.responseJSON.errors.join("<br/>") : transport.responseText;

          fireError( e, msg );
          
          return;
        }

        e.element().up("tr").remove();
        fireSuccessOverlay( transport.responseJSON.success == "accepted" ? "request accepted !" : "request denied !");
      }
    });
  });
});
//-->
</script>
<?php
  }
}


$form = new PouetFormProcessor();
$form->renderForm = false;

$box = new PouetBoxAdminModificationRequests( );
$form->Add( "adminModReq", $box );

if ($currentUser && $currentUser->CanEditItems())
{
  if (@$_POST["partial"])
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
      $response["success"] = @$_POST["requestAccept"] ? "accepted" : "denied";
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
