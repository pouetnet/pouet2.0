<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

class PouetBoxModificationRequest extends PouetBox
{
  function PouetBoxModificationRequest()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_modificationrequest";
    $this->title = "submit a change request";
    $this->formifier = new Formifier();
    $this->fields = array();
  }

  function Validate( $data )
  {
    global $currentUser;

    $errormessage = array();

    if (!$currentUser)
    {
  	  $errormessage[]="you need to be logged in first.";
  	  return $errormessage;
  	}

  	return array();
  }

  function Commit($data)
  {
    $post = array();

    global $REQUESTTYPES;
    if ($REQUESTTYPES[ $_POST["requestType"] ])
    {
      $error = $REQUESTTYPES[ $_POST["requestType"] ]::ValidateRequest($data,$post);
      if ($error) return $error;
    }
    else
    {
      return array("no such request type!");
    }
    $a = array();
    $a["requestType"] = $data["requestType"];
    if($_REQUEST["prod"])
    {
      $a["itemID"] = (int)$_REQUEST["prod"];
      $a["itemType"] = "prod";
    }
    $a["requestDate"] = date("Y-m-d H:i:s");
    $a["userID"] = get_login_id();

    $a["requestBlob"] = serialize($post);

    global $reqID;
    $reqID = SQLLib::InsertRow("modification_requests",$a);

    return array();
  }
  function LoadFromDB()
  {
    $this->fields = array(
      "requestType"=>array(
        "type"=>"select",
        "name"=>"whatchu want",
        "assoc"=>true,
      ),
    );

    global $REQUESTTYPES;
    
    foreach($REQUESTTYPES as $k=>$v)
    {
      if ($_REQUEST["prod"] && $v::GetItemType()=="prod") $this->fields["requestType"]["fields"][$k] = $v::Describe();
    }
    
    $this->fields["requestType"]["fields"]["other"] = "other request...";
    
    foreach($_POST as $k=>$v)
      if ($this->fields[$k])
        $this->fields[$k]["value"] = $v;

  }

  function Render()
  {
    $error = "";
    
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";

    echo "  <h2>".$this->title.": ";
    if ($_REQUEST["prod"])
    {
      $prod = PouetProd::Spawn($_REQUEST["prod"]);
      if (!$prod) die("no such prod!");
      echo $prod->RenderSingleRowShort();
    }
    echo "</h2>\n";

    $error = "";
    if(!$_POST["requestType"])
    {
      echo "  <div class='content'>\n";
      if(count($this->fields["requestType"]["fields"]))
        $this->formifier->RenderForm( $this->fields );
      else
      {
        echo "you need to select something to request about first !";
        $error = " ";
      }
      echo "  </div>\n";
    }
    else
    {
      $this->fields["requestType"]["type"] = "statichidden";
      echo "  <div class='content'>\n";
      $this->formifier->RenderForm( $this->fields );
      echo "  </div>\n";
      echo "  <h2>more data</h2>\n";
      echo "  <div class='content'>\n";
      $fields = array();
      
      $js = "";
      global $REQUESTTYPES;
      if ($REQUESTTYPES[ $_POST["requestType"] ])
      {
        $error = $REQUESTTYPES[ $_POST["requestType"] ]::GetFields($_REQUEST,$fields,$js);
      }
      else
      {
        $error = "no such request type !";
      }

      if ($fields && !$error)
      {
        foreach($_POST as $k=>$v)
          if ($fields[$k])
            $fields[$k]["value"] = $v;
        $this->formifier->RenderForm($fields);
      }
      if ($error)
        echo $error;
      echo "  </div>\n";

    }
    
    if ($js)
    {
      echo "<script type='text/javascript'>\n";
      echo "<!--\n";
      echo $js;
      echo "//-->\n";
      echo "</script>\n";
    }
  
    if (!$error)
      echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};

$TITLE = "submit a modification request";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$form = new PouetFormProcessor();

$form->successMessage = "your request was recorded and will be processed by a glöperator eventually !";

if ($_POST["requestType"] == "other")
{
  redirect("topic.php?which=".(int)FIXMETHREAD_ID."#pouetbox_bbspost");
  exit();
}

if ($_REQUEST["prod"])
  $form->SetSuccessURL( "prod.php?which=".(int)$_REQUEST["prod"], false );
else
  $form->SetSuccessURL( "", false );

$form->Add( "logo", new PouetBoxModificationRequest() );

if ($currentUser && $currentUser->CanSubmitItems() && (int)$_POST["finalStep"]==1)
  $form->Process();
else
  unset( $_POST[ PouetFormProcessor::fieldName ] );

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
