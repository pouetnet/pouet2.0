<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-bbs-post.php");

class PouetBoxLogoVote extends PouetBox 
{
  public $logo;
  function __construct($logo) 
  {
    parent::__construct();
    $this->uniqueID = "";
    $this->classes[] = "logovote";
    $this->logo = $logo;
    $this->title = $logo->file;
  }

  function RenderContent() {
    echo "<img src=".POUET_CONTENT_URL."logos/"._html($this->logo->file)." alt='logo'/>";
	}
  function RenderFooter() {
    echo "  <div class='foot'>";
    echo "    <form action='".$_SERVER["REQUEST_URI"]."' method='post'>";
    $csrf = new CSRFProtect();
    $csrf->PrintToken();
    echo "      <input name='logoID' type='hidden' value='".(int)$this->logo->id."'/>";
    echo "      <input name='submit' type='submit' value='rulez'/>";
    echo "      <input name='submit' type='submit' value='sucks'/>";
    echo "    </form>\n";
    echo "  </div>\n";
    echo "</div>\n";
  }

};


class PouetBoxLogoLama extends PouetBox 
{
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_logolama";
    $this->title = "no logo left, you are now a l4m4h";
  }

  function RenderContent() 
  {
    $lama_pictures = array(
      'logos-lamer.jpg',
      'logos-lama.jpg',
      'logos-dalai-lama.jpg',
      'logos-lamerbus.jpg',
      'logos-lamerst-by-charlie.jpg',
      'logos-lamercream.jpg',
      'logos-lamerjewels.jpg',
      'logos-lamercow-by-everybody.jpg',
    );

    echo "<img src='".POUET_CONTENT_URL."gfx/".$lama_pictures[array_rand($lama_pictures)]."' alt='Lamer picture'/>";
	}
  function RenderFooter() {
    echo "  <div class='foot'><a href='".POUET_ROOT_URL."'>get back</a></div>\n";
    echo "</div>\n";
  }

};

$sel = null;
if ($currentUser)
{
  $sel = new SQLSelect();
  $sel->AddField("logos.id as id");
  $sel->AddField("logos.file as file");
  $sel->AddTable("logos");
  $sel->AddJoin("LEFT","logos_votes",sprintf_esc("logos_votes.logo = logos.id AND logos_votes.user = %d",$currentUser->id));
  $sel->AddWhere("logos_votes.id IS NULL");
  $sel->AddOrder("RAND()");

  if (@$_POST["logoID"] && @$_POST["submit"])
  {
    $vote = 0;
    if ($_POST["submit"] == "rulez") $vote = 1;
    if ($_POST["submit"] == "sucks") $vote = -1;

    $csrf = new CSRFProtect();
    if ($vote && $csrf->ValidateToken())
    {
      SQLLib::Query(sprintf_esc("delete from logos_votes where logo = %d and user = %d",$_POST["logoID"],$currentUser->id));

      $a = array();
      $a["logo"] = (int)$_POST["logoID"];
      $a["user"] = $currentUser->id;
      $a["vote"] = $vote;
      SQLLib::InsertRow("logos_votes",$a);
    }

    SQLLib::Query(sprintf_esc("update logos set vote_count = (select sum(vote) from logos_votes where logo = %d) where id = %d",(int)$_POST["logoID"],(int)$_POST["logoID"]));

    // ajax
    if ($_POST["partial"]==1)
    {
      $s = clone $sel;
      $visibleLogos = $_POST["visibleLogos"];
      foreach($visibleLogos as $k=>$v) $visibleLogos[$k] = (int)$v;
      $s->AddWhere(sprintf_esc("logos.id not in (%s)",implode(",",$visibleLogos)));
      $s->SetLimit(1);
      $logo = SQLLib::SelectRow($s->GetQuery());

      if ($logo)
      {
        $box = new PouetBoxLogoVote($logo);
        $box->Render();
      }
      else
      {
        $box = new PouetBoxLogoLama();
        $box->Render();
      }
      exit();
    }
  }
}

$TITLE = "vote for logos !";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if ($sel)
{
  $s = clone $sel;
  $s->SetLimit(5);
  $logos = SQLLib::SelectRows($s->GetQuery());

  if ($logos)
  {
    foreach($logos as $logo)
    {
      $box = new PouetBoxLogoVote($logo);
      $box->Render();
    }
  }
  else
  {
    $box = new PouetBoxLogoLama();
    $box->Render();
  }

?>
<script>
<!--
function InstrumentForm(form)
{
  if (!form) return;
  form.select("input[type=submit]").invoke("observe","click",function(e) {
    form.select("input[type=submit]").invoke("setAttribute","clicked","");
    e.element().setAttribute("clicked","true");
  });
  form.observe("submit",function(e){
    e.stop();

    var visibleLogos = $$("input[name='logoID'][type='hidden']").pluck("value");

    var values = $H(Form.serialize(form,true));
    values.set("submit",form.select("input[type=submit][clicked=true]").first().value);
    values.set("partial",1);
    values.set("visibleLogos[]",visibleLogos);

    new Ajax.Request(form.action,{
      method: form.method,
      parameters: values,
      onSuccess: function(transport) {
        form.up(".logovote").remove();
        if (transport.responseText.length)
        {
          var div = new Element("div").update(transport.responseText);
          InstrumentForm( div.down("form") );

          console.log( $("content").select("div").length );
          console.log( div.down("#pouetbox_logolama") );

          if (div.down("#pouetbox_logolama"))
          {
            if ($("content").select("div.logovote").length == 0)
              $("content").insert(div);
          }
          else
          {
            $("content").insert(div);
          }
        }
      },
    });
  });
}
document.observe("dom:loaded",function(){
  $$("form").each(function(item){
    InstrumentForm(item);
  });
});
//-->
</script>
<?php
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
