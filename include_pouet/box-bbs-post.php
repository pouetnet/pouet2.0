<?php

class PouetBoxBBSPost extends PouetBox {
  var $topic;
  function __construct($topic) {
    parent::__construct();
    $this->topic = (int)$topic;
    $this->uniqueID = "pouetbox_bbspost";
    $this->title = "post a new reply";
  }
  use PouetForm;
  function Validate($post)
  {
    global $currentUser;
    if (!$currentUser)
      return array("you have to be logged in!");

    if (!$currentUser->CanPostInBBS())
      return array("not allowed lol.");

    $message = trim($post["message"]);

    if (!$message)
      return array("not too meaningful, is it...");

    if ($currentUser->glops == 0 && strstr($message,"://")!==false)
      return array("you need at least 1 glöp to post links !");

    $topic = SQLLib::SelectRow(sprintf_esc("SELECT * FROM bbs_topics where id=%d",$this->topic));
    if (!$topic)
      return array("you sneaky bastard you >_<");
    if ($topic->closed)
      return array("closed, capisci?");

    $r = SQLLib::SelectRow(sprintf_esc("SELECT author,topic,post FROM bbs_posts WHERE topic = %d ORDER BY added DESC LIMIT 1",(int)$this->topic));

    if ($r && $r->author == get_login_id() && $r->post == $message)
      return array("ERROR! DOUBLEPOST == ROB IS JARIG!");

    return array();
  }

  function Commit($post)
  {
    $message = trim($post["message"]);

    $r = SQLLib::SelectRow("SELECT count(0) as c FROM bbs_posts WHERE topic=".$this->topic);

  	$a = array();
  	$a["userlastpost"] = get_login_id();
  	$a["lastpost"] = date("Y-m-d H:i:s");
  	$a["count"] = $r->c;

    SQLLib::UpdateRow("bbs_topics",$a,"id=".$this->topic);

  	$a = array();
  	$a["added"] = date("Y-m-d H:i:s");
  	$a["author"] = get_login_id();
  	$a["post"] = $message;
  	$a["topic"] = $this->topic;

    SQLLib::InsertRow("bbs_posts",$a);

    @unlink("cache/pouetbox_latestbbs.cache");

    return array();
  }

  function RenderBody()
  {
    global $currentUser;
    if (!$currentUser || !$currentUser->CanPostInBBS())
      return;

    echo "<form action='add.php' method='post' id='frmBBSPost'>\n";

    $csrf = new CSRFProtect();
    $csrf->PrintToken();

    echo "<div class='content'>\n";
    echo " <input type='hidden' name='which' value='".(int)$this->topic."'>\n";
    echo " <input type='hidden' name='type' value='post'>\n";
    echo " message:\n";
    echo " <textarea name='message' id='message'>";
    if ($this->topic == FIXMETHREAD_ID)
    {
      if (@$_GET["fromProd"])
      {
        printf("There is a problem with [url=%sprod.php?which=%d]this prod[/url]: ...",POUET_ROOT_URL,$_GET["fromProd"]);
      }
      else if (@$_GET["fromGroup"])
      {
        printf("There is a problem with [url=%sgroups.php?which=%d]this group[/url]: ...",POUET_ROOT_URL,$_GET["fromGroup"]);
      }
    }
    echo "</textarea>\n";
    echo " <div><a href='faq.php#BB Code'><b>BB Code</b></a> is allowed here</div>\n";
    echo "</div>\n";
    echo "<div class='foot'>\n";
    echo " <script>\n";
    echo " <!--\n";
    echo "   document.observe('dom:loaded',function(){ AddPreviewButton($('submit')); });\n";
    echo " //-->\n";
    echo " </script>\n";
    echo " <input type='submit' value='Submit' id='submit'>";
    echo "</div>\n";
    echo "</form>\n";
  }

};

?>
