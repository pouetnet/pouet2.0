<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-modalmessage.php");

class PouetBoxModificationRequest extends PouetBox 
{
  function PouetBoxModificationRequest() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_modificationrequest";
    $this->title = "submit a change request";
    $this->formifier = new Formifier();
    $this->fields = array();
    $this->fieldsRequestTypes = array(
      "prod_add_link" => "add a new extra link to a prod",
      "prod_change_link" => "change an existing extra link",
      "prod_change_field" => "change basic info about a prod",
      "prod_del" => "delete a prod",
    );
  }
  
  function ParsePostMessage( $data )
  {
    global $currentUser;
    
    $errormessage = array();
    
    if (!$currentUser)
    {
  	  $errormessage[]="you need to be logged in first.";
  	  return $errormessage;
  	}
  	
  	if ($data["finalStep"]!=1)
  	  return array();
    
    if (count($errormessage))
      return $errormessage;

    $a = array();
    $a["requestType"] = $data["requestType"];
    if($_REQUEST["prod"])
      $a["itemID"] = (int)$_REQUEST["prod"];
    $a["requestDate"] = date("Y-m-d H:i:s");
    $a["userID"] = get_login_id();
    
    $post = $data;
    unset($post["requestType"]);
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
        "fields"=>$this->fieldsRequestTypes,
        "name"=>"whatchu want",
        "assoc"=>true,
      ),
    );
    foreach($_POST as $k=>$v)
      if ($this->fields[$k])
        $this->fields[$k]["value"] = $v;
       
  }

  function Render() 
  {
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    
    echo "  <h2>".$this->title.": ";
    if ($_REQUEST["prod"])
    {
      $prod = PouetProd::Spawn($_REQUEST["prod"]);
      if (!$prod) die("no such prod!");
      echo _html($prod->name);
      if ($prod->groups)
        echo " by ".$prod->RenderGroupsPlain();
    }
    echo "</h2>\n";
    
    if(!$_POST["requestType"])
    {
      echo "  <div class='content'>\n";
      foreach($this->fields["requestType"]["fields"] as $k=>$v)
      {
        if ($prod && strpos($k,"prod")!==0) unset($this->fields["requestType"]["fields"]);
      }
      $this->formifier->RenderForm( $this->fields );
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
      switch($_POST["requestType"])
      {
        case "prod_add_link":
          $fields = array(
            "newLinkKey" => array(
              "name"=>"link description (youtube, source, linux port, etc)",
            ),
            "newLink" => array(
              "name"=>"link url",
            ),
            "finalStep" => array(
              "type"=>"hidden",
              "value"=>1,
            ),
          );
          break;
      }
      if ($fields)
      {
        foreach($_POST as $k=>$v)
          if ($fields[$k])
            $fields[$k]["value"] = $v;
        $this->formifier->RenderForm($fields);
      }
      echo "  </div>\n";
      
    }

    echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};

$TITLE = "submit a modification request";

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if ($currentUser && $currentUser->CanSubmitItems())
{
  $box = new PouetBoxModificationRequest();

  $errors = array();
  if ($_POST)
  {
    $errors = $box->ParsePostMessage( $_POST );
    if (count($errors))
    {
      $msg = new PouetBoxModalMessage( true );
      $msg->title = "An error has occured:";
      $msg->message = "<ul><li>".implode("</li><li>",$errors)."</li></ul>";
      $msg->Render();
    }
    else if($reqID)
    {
      $msg = new PouetBoxModalMessage( true );
      $msg->title = "Success!";
      $msg->message = "your request has been stored (#".$reqID.") and will be reviewed by a glöperator !";
      $msg->Render();
    }
  }

  $box->Load();
  if (!count($errors) && !$reqID)
  {
    printf("<form action='%s' method='post' enctype='multipart/form-data'>\n",$_SERVER["REQUEST_URI"]);
    $box->Render();
    printf("</form>");
  }


?>
<script type="text/javascript">
document.observe("dom:loaded",function(){
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