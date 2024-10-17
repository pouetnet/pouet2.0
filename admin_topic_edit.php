<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-group-submit.php");

if ($currentUser && !$currentUser->CanEditBBS())
{
  redirect("topic.php?which=".(int)$_GET["which"]);
  exit();
}

class PouetBoxAdminEditTopic extends PouetBox
{
  public $id;
  public $party;
  public $formifier;
  public $fields;
  public $categories;
  public $topic;
  function __construct( $id )
  {
    parent::__construct();
    $this->id = (int)$id;
    $this->uniqueID = "pouetbox_edittopic";
    $this->topic = SQLLib::SelectRow(sprintf_esc("select * from bbs_topics where id = %d",$this->id));
    $this->title = "edit this topic: "._html($this->topic->topic);
    $this->formifier = new Formifier();
    $this->fields = array();

    $row = SQLLib::selectRow("DESC bbs_topics category");
    $this->categories = enum2array($row->Type);
  }

  function ValidateInput( $data )
  {
    $errormessage = array();
    return $errormessage;
  }
  use PouetForm;
  function Validate( $data )
  {
    global $groupID,$currentUser;

    if (!$currentUser)
      return array("you have to be logged in !");

    if (!$currentUser->CanEditBBS())
      return array("not allowed lol !");

    return array();
  }
  function Commit($data)
  {
    global $groupID;

    $a = array();
    $a["category"] = $data["category"];
    $a["closed"] = (int)($data["closed"]=="on");
    SQLLib::UpdateRow("bbs_topics",$a,"id=".$this->topic->id);

    $stateChange = array();
    foreach($a as $k=>$v)
    {
      if ($this->topic->$k == $v)
      {
        continue;
      }
      $stateChange[$k] = array("old"=>$this->topic->$k,"new"=>$v);
    }
    gloperator_log( "topic", $this->topic->id, "topic_edit", $stateChange);

    $topicID = $this->topic->id;
    flush_cache("pouetbox_latestbbs.cache",function($i)use($topicID){ return $i->id == $topicID; } );
    
    return array();
  }

  function ParsePostMessage( $data )
  {
    global $groupID,$currentUser;

    $errormessages = $this->Validate($data);
    if (count($errormessages))
      return $errormessages;

    return $this->Commit($data);
  }
  function LoadFromDB()
  {
    global $THREAD_CATEGORIES;

    $this->fields = array(
      "category"=>array(
        "name"=>"topic category",
        "type"=>"select",
        "fields"=>$this->categories,
        "value"=>$this->topic->category,
      ),
      "closed"=>array(
        "name"=>"topic closed",
        "type"=>"checkbox",
        "value"=>$this->topic->closed,
      ),
    );
    foreach($_POST as $k=>$v)
      if ($this->fields[$k])
        $this->fields[$k]["value"] = $v;
  }

  function Render()
  {
    global $currentUser;
    if (!$currentUser)
      return;

    if (!$currentUser->CanSubmitItems())
      return;

    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";

    echo "  <h2>".$this->title."</h2>\n";
    echo "  <div class='content'>\n";
    $this->formifier->RenderForm( $this->fields );
    echo "  </div>\n";

    echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
};

$form = new PouetFormProcessor();

$form->SetSuccessURL( "topic.php?which=".(int)$_GET["which"], true );

$box = new PouetBoxAdminEditTopic( $_GET["which"] );
$form->Add( "topic", $box );

if ($currentUser && $currentUser->CanEditItems())
  $form->Process();

$TITLE = "edit a topic: ".$box->topic->topic;

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
