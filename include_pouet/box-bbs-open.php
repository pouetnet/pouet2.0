<?php
class PouetBoxBBSOpen extends PouetBox
{
  public $topic;
  public $categories;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_bbsopen";
    $this->title = "open a new bbs thread";

    $row = SQLLib::selectRow("DESC bbs_topics category");
    $this->categories = enum2array($row->Type);
  }

  function ParsePostMessage($post)
  {
    global $currentUser;
    if (!$currentUser)
      return "you have to be logged in!";

    if (!$currentUser->CanOpenNewBBSTopic())
      return "not allowed lol.";

    $message = trim($post["message"]);
    if (!$message)
      return "not too meaningful, is it...";

    if ($currentUser->glops == 0 && strstr($message,"://")!==false)
      return array("you need at least 1 glöp to post links !");

    $title = trim($post["topic"]);
    if (strlen($title) < 2)
      return "not too meaningful, is it...";

    $r = SQLLib::SelectRow(sprintf_esc("SELECT id FROM bbs_topics where topic='%s'",$title));
    if ($r)
      return "DOUBLEPOST == ROB IS JARIG";

  	$a = array();
  	$a["topic"] = $title;
  	$a["category"] = $post["category"];
  	$a["userfirstpost"] = $a["userlastpost"] = get_login_id();
  	$a["firstpost"] = $a["lastpost"] = date("Y-m-d H:i:s");

    $id = SQLLib::InsertRow("bbs_topics",$a);

  	$a = array();
  	$a["added"] = date("Y-m-d H:i:s");
  	$a["author"] = get_login_id();
  	$a["post"] = $message;
  	$a["topic"] = $id;

    SQLLib::InsertRow("bbs_posts",$a);

    @unlink("cache/pouetbox_latestbbs.cache");

    return "";

  }

  function RenderBody()
  {
    global $currentUser;
    if (!$currentUser)
      return;

    if (!$currentUser->CanPostInBBS())
      return;

    echo "<form action='add.php' method='post'>\n";

    $csrf = new CSRFProtect();
    $csrf->PrintToken();

    echo "<div class='content'>\n";
    echo " <input type='hidden' name='type' value='bbs'>\n";

    echo " <label for='topic'>topic:</label>\n";
    echo " <input name='topic' id='topic'/>\n";

    echo " <label for='category'>category:</label>\n";
    echo " <select name='category' id='category'>\n";
    foreach($this->categories as $v)
      printf("<option value='%s'>%s</option>",_html($v),_html($v));
    echo " </select>\n";

    echo " <label for='message'>message:</label>\n";
    echo " <textarea name='message' id='message'></textarea>\n";

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
