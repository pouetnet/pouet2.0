<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

class PouetBoxModificationRequest extends PouetBox
{
  public $formifier;
  public $fields;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_modificationrequest";
    $this->title = "submit a change request";
    $this->formifier = new Formifier();
    $this->fields = array();
  }
  use PouetForm;

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
    if ($REQUESTTYPES[ $_REQUEST["requestType"] ])
    {
      $error = $REQUESTTYPES[ $_REQUEST["requestType"] ]::ValidateRequest($data,$post);
      if ($error) return $error;
    }
    else
    {
      return array("no such request type!");
    }
    $a = array();
    $a["requestType"] = $data["requestType"];
    if(@$_REQUEST["prod"])
    {
      $a["itemID"] = (int)$_REQUEST["prod"];
      $a["itemType"] = "prod";
    }
    else if(@$_REQUEST["group"])
    {
      $a["itemID"] = (int)$_REQUEST["group"];
      $a["itemType"] = "group";
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
      if (@$_REQUEST["prod"]  && $v::GetItemType()=="prod" ) $this->fields["requestType"]["fields"][$k] = $v::Describe();
      if (@$_REQUEST["group"] && $v::GetItemType()=="group") $this->fields["requestType"]["fields"][$k] = $v::Describe();
    }
    
    foreach($_REQUEST as $k=>$v)
    {
      if (@$this->fields[$k])
      {
        $this->fields[$k]["value"] = $v;
      }
    }
  }

  function Render()
  {
    $error = "";
    $js = "";
    
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";

    echo "  <h2>".$this->title.": ";
    if (@$_REQUEST["prod"])
    {
      $prod = PouetProd::Spawn($_REQUEST["prod"]);
      if (!$prod) die("no such prod!");
      echo $prod->RenderSingleRowShort();
    }
    if (@$_REQUEST["group"])
    {
      $group = PouetGroup::Spawn($_REQUEST["group"]);
      if (!$group) die("no such group!");
      echo $group->RenderLong();
    }
    echo "</h2>\n";

    $error = "";
    if(!@$_REQUEST["requestType"])
    {
      echo "  <div class='content'>\n";
      global $REQUESTTYPES;
      echo "<label>whatchu want ?</label>\n";
      echo "<ul>\n";
      foreach($REQUESTTYPES as $k=>$v)
      {
        if (@$_REQUEST["prod"] && $v::GetItemType()=="prod")
          printf("  <li><a href='%s&amp;requestType=%s'>%s</a></li>",selfPath(),_html($k),_html($v::Describe()));
        if (@$_REQUEST["group"] && $v::GetItemType()=="group")
          printf("  <li><a href='%s&amp;requestType=%s'>%s</a></li>",selfPath(),_html($k),_html($v::Describe()));
      }
      printf("  <li><a href='%s&amp;requestType=%s'>%s</a></li>",selfPath(),_html("other"),_html("other request..."));
      echo "</ul>\n";
      $error = " ";
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
      
      global $REQUESTTYPES;
      if ($REQUESTTYPES[ $_REQUEST["requestType"] ])
      {
        $error = $REQUESTTYPES[ $_REQUEST["requestType"] ]::GetFields($_REQUEST,$fields,$js);
      }
      else
      {
        $error = "no such request type !";
      }

      if ($fields && !$error)
      {
        foreach($_POST as $k=>$v)
          if (@$fields[$k])
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

if (@$_REQUEST["requestType"] == "other")
{
  if (@$_REQUEST["prod"])
  {
    redirect("topic.php?which=".(int)FIXMETHREAD_ID."&fromProd=".(int)$_REQUEST["prod"]."#pouetbox_bbspost");
  }
  else if (@$_REQUEST["group"])
  {
    redirect("topic.php?which=".(int)FIXMETHREAD_ID."&fromGroup=".(int)$_REQUEST["group"]."#pouetbox_bbspost");
  }
  exit();
}

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$form = new PouetFormProcessor();

$form->successMessage = 
  "your request was recorded and will be processed by a glöperator eventually !</a> <br/>". // l33t h4x
  "you can keep track of the status of your requests <a href='account.php#pouetbox_accountreq'>on your accounts page !";

if (@$_REQUEST["prod"])
  $form->SetSuccessURL( "prod.php?which=".(int)$_REQUEST["prod"], false );
else if (@$_REQUEST["group"])
  $form->SetSuccessURL( "groups.php?which=".(int)$_REQUEST["group"], false );
else
  $form->SetSuccessURL( "", false );

$form->Add( "logo", new PouetBoxModificationRequest() );

if ($currentUser && $currentUser->CanSubmitItems() && (int)@$_POST["finalStep"]==1)
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
