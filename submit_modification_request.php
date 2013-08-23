<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

if (!POUET_TEST)
{
  header("Location: index.php");
  exit();
}

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
    $a = array();
    $a["requestType"] = $data["requestType"];
    if($_REQUEST["prod"])
    {
      $a["itemID"] = (int)$_REQUEST["prod"];
      $a["itemType"] = "prod";
    }
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
        "name"=>"whatchu want",
        "assoc"=>true,
      ),
    );

    global $REQUESTTYPES;
    
    foreach($REQUESTTYPES as $k=>$v)
    {
      if ($_REQUEST["prod"] && $v::GetItemType()=="prod") $this->fields["requestType"]["fields"][$k] = $v::Describe();
    }
    
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
      
      global $REQUESTTYPES;
      if ($REQUESTTYPES[ $_POST["requestType"] ])
      {
        $error = $REQUESTTYPES[ $_POST["requestType"] ]::GetFields($_REQUEST,$fields);
      }
      
      /*
      switch($_POST["requestType"])
      {
        case "prod_add_link":
          $fields = array(
            "newLinkKey" => array(
              "name"=>"link description (youtube, source, linux port, etc)",
            ),
            "newLink" => array(
              "name"=>"link url",
              "type"=>"url",
            ),
            "finalStep" => array(
              "type"=>"hidden",
              "value"=>1,
            ),
          );
          break;
        case "prod_change_link":
          if ($_POST["linkID"])
          {
            $l = SQLLib::SelectRow(sprintf_esc("select * from downloadlinks where id = %d",$_POST["linkID"]));
            $fields = array(
              "linkID" => array(
                "type"=>"hidden",
                "value"=>(int)$_POST["linkID"],
              ),
              "newLinkKey" => array(
                "type"=>"text",
                "value"=>$l->type,
              ),
              "newLink" => array(
                "type"=>"url",
                "value"=>$l->link,
              ),
              "finalStep" => array(
                "type"=>"hidden",
                "value"=>1,
              ),
            );
          }
          else
          {
            $l = SQLLib::SelectRows(sprintf_esc("select * from downloadlinks where prod = %d",$prod->id));
            foreach($l as $v)
              $links[$v->id] = sprintf("[%s] %s",$v->type,$v->link);
            if ($links)
            {
              $fields = array(
                "linkID" => array(
                  "name"=>"select link you want to edit",
                  "type"=>"select",
                  "fields"=>$links,
                  "assoc"=>true,
                ),
              );
            }
            else
            {
              $error = "this prod has no extra links to change !";
            }
          }
          break;
        case "prod_remove_link":
          $l = SQLLib::SelectRows(sprintf_esc("select * from downloadlinks where prod = %d",$prod->id));
          foreach($l as $v)
            $links[$v->id] = sprintf("[%s] %s",$v->type,$v->link);
          if ($links)
          {
            $fields = array(
              "linkID" => array(
                "name"=>"select link you want removed",
                "type"=>"select",
                "fields"=>$links,
                "assoc"=>true,
              ),
              "reason" => array(
                "name"=>"why should this link be deleted",
                "type"=>"textarea",
                "info"=>"moderator's note: abuse of this feature will result in retaliation. have a nice day.",
              ),
              "finalStep" => array(
                "type"=>"hidden",
                "value"=>1,
              ),
            );
          }
          else
          {
            $error = "this prod has no extra links to change !";
          }
          break;
        case "prod_del":
          {
            $fields = array(
              "reason" => array(
                "name"=>"why should this prod be deleted",
                "type"=>"textarea",
                "info"=>"moderator's note: abuse of this feature will result in retaliation. have a nice day.",
              ),
              "finalStep" => array(
                "type"=>"hidden",
                "value"=>1,
              ),
            );
          } break;
      }
      */
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

$form->SetSuccessURL( "", false );

$form->Add( "logo", new PouetBoxModificationRequest() );

if ($currentUser && $currentUser->CanSubmitItems() && (int)$_POST["finalStep"]==1)
  $form->Process();
else
  unset( $_POST[ PouetFormProcessor::fieldName ] );

if (get_login_id())
{
  $form->Display();
?>
<script type="text/javascript">
document.observe("dom:loaded",function(){
});
</script>
<?
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
