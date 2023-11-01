<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-party-submit.php");

if ($currentUser && !$currentUser->IsModerator())
{
  redirect("index.php");
  exit();
}

class PouetBoxAdminEditFAQ extends PouetBox
{
  public $id;
  public $item;
  public $cateogries;
  public $fields;
  public $formifier;
  public $categories;
  function __construct( $id )
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_admineditfaq";
    $this->title = "edit faq";
    $this->id = $id;
    $this->formifier = new Formifier();
    
    $row = SQLLib::selectRow("DESC faq category");
    $this->categories = enum2array($row->Type);
    
    $this->fields = array(
      "category"=>array(
        "name"=>"category",
        "type"=>"select",
        "fields"=>$this->categories,
      ),
      "question"=>array(
        "name"=>"question",
      ),
      "answer"=>array(
        "name"=>"answer",
        "type"=>"textarea",
      ),
      "deprecated"=>array(
        "name"=>"is hidden?",
        "type"=>"checkbox",
      ),
    );    
    
    if ($_POST)
    {
      foreach($_POST as $k=>$v)
        if (@$this->fields[$k])
          $this->fields[$k]["value"] = $v;
    }
  }
  use PouetForm;
  function Commit($data)
  {
    global $currentUser;
    
    $a = array();
    $a["category"] = $data["category"];
    $a["question"] = $data["question"];
    $a["answer"] = $data["answer"];
    $a["deprecated"] = (int)($data["deprecated"] == "on");
    if ($data["faqID"])
    {
      //gloperator_log( "faq", $data["faqID"], "faq_edit" );
      SQLLib::UpdateRow("faq",$a,"id=".(int)$data["faqID"]);
    }
    else
    {
      //gloperator_log( "faq", 0, "faq_add" );
      SQLLib::InsertRow("faq",$a);
    }

    return array();
  }
  function LoadFromDB()
  {
    if ($this->id)
    {
      $s = new BM_Query();
      $s->AddTable("faq");
      $s->AddWhere(sprintf_esc("id = %d",$this->id));
      $item = $s->perform();
      $this->item = $item[0];
      
      $this->fields["category"]["value"] = $this->item->category;
      $this->fields["question"]["value"] = $this->item->question;
      $this->fields["answer"]["value"] = $this->item->answer;
      $this->fields["deprecated"]["value"] = $this->item->deprecated;
    }
  }
  function Render()
  {
    global $REQUESTTYPES;
    echo "<div id='".$this->uniqueID."' class='pouettbl'>\n";
    echo "  <h2>".$this->title;
    if ($this->id)
    {
      printf(": #%d",$this->id);
      printf("<input type='hidden' name='faqID' value='%d'/>",$this->id);
    }
    else
    {
      echo " - add new";
    }
    echo "</h2>";
    echo "  <div class='content'>\n";
    $this->formifier->RenderForm( $this->fields );
    echo "  </div>\n";
    echo "  <div class='foot'><input type='submit' value='Submit' /></div>";
    echo "</div>\n";
  }
}

class PouetBoxAdminEditFAQList extends PouetBox
{
  public $items;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_admineditfaqlist";
    $this->title = "edit faq";
  }
  function LoadFromDB()
  {
    $s = new BM_Query();
    $s->AddTable("faq");
    $s->AddOrder("category, id");
    $this->items = $s->perform();
  }
  use PouetForm;
  function Render()
  {
    global $REQUESTTYPES;
    echo "<table id='".$this->uniqueID."' class='boxtable'>\n";
    echo "  <tr>\n";
    echo "    <th colspan='6'>".$this->title."</th>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <th>id</th>\n";
    echo "    <th>category</th>\n";
    echo "    <th>question</th>\n";
    echo "    <th>hidden</th>\n";
    echo "  </tr>\n";
    foreach($this->items as $r)
    {
      echo "  <tr>\n";
      echo "    <td>".$r->id."</td>\n";
      echo "    <td>".$r->category."</td>\n";
      echo "    <td><a href='admin_faq.php?id=".(int)$r->id."'>".$r->question."</a></td>\n";
      echo "    <td>".($r->deprecated?"yes":"&nbsp;")."</td>\n";
      echo "  </tr>\n";
    }
    echo "  <tr>\n";
    echo "    <td colspan='6'><a href='admin_faq.php?new=add'>add new item</a></th>\n";
    echo "  </tr>\n";
    echo "</table>\n";
  }
}


$form = new PouetFormProcessor();

if (@$_GET["id"] || @$_GET["new"]=="add")
  $form->Add( "adminModFaqID", new PouetBoxAdminEditFAQ( @$_GET["id"] ) );
else
  $form->Add( "adminModFaq", new PouetBoxAdminEditFAQList( ) );

if ($currentUser && $currentUser->IsModerator())
{
  $form->SetSuccessURL( "admin_faq.php", true );
  $form->Process();
}

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
