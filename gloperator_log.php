<?php
require_once("bootstrap.inc.php");

class PouetBoxGloperatorLog extends PouetBox 
{
  public $logs;
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_gloperatorlog";
    $this->title = "edits for this "._html($_GET["what"]).":";
  }

  function LoadFromDB()
  {
    $q = new BM_Query();
    $q->AddField("gloperator_log.*");
    $q->AddTable("gloperator_log");
    $q->AddWhere(sprintf_esc("itemType = '%s'",$_GET["what"]));
    $q->AddWhere(sprintf_esc("itemID = %d",$_GET["which"]));
    $q->attach(array("gloperator_log"=>"gloperatorID"),array("users as gloperator"=>"id"));
    $q->AddOrder("date desc");

    $this->logs = $q->perform();
  }
  function RenderBody() {
    global $THREAD_CATEGORIES;
    echo "<table class='boxtable'>\n";
    $n = 0;
    echo "<tr>\n";
    echo "  <th>date</th>\n";
    echo "  <th>glöperator</th>\n";
    echo "  <th>action</th>\n";
    echo "  <th>more info</th>\n";
    echo "</tr>\n";
    foreach ($this->logs as $r) {
      echo "<tr>\n";
      echo "  <td>"._html($r->date)."</td>\n";
      echo "  <td>".$r->gloperator->PrintLinkedAvatar()." ".$r->gloperator->PrintLinkedname()."</td>\n";
      echo "  <td>"._html($r->action)."</td>\n";
      echo "  <td>";
      
      global $REQUESTTYPES;
      if (@$REQUESTTYPES[$r->action])
      {
        echo $REQUESTTYPES[$r->action]::Display( $_GET["which"], json_decode($r->additionalData,true) ); 
      }
      else
      {
        switch($r->action)
        {
          default:
            {
              echo _html($r->additionalData);
            } break;
        }
      }
      echo "</td>";
      echo "</tr>\n";
    }
    echo "</table>\n";
    echo "<div class='foot'>";
    switch ($_GET["what"])
    {
      case "prod": printf("<a href='prod.php?which=%d'>back to the prod</a>",$_GET["which"]); break;
      case "group": printf("<a href='groups.php?which=%d'>back to the group</a>",$_GET["which"]); break;
      case "topic": printf("<a href='topic.php?which=%d'>back to the topic</a>",$_GET["which"]); break;
      case "party": printf("<a href='party.php?which=%d'>back to the party</a>",$_GET["which"]); break;
    }
    echo "</div>\n";
  }
};

$TITLE = "glöperator log";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

if (@$_GET["what"] && @$_GET["which"])
{
  $box = new PouetBoxGloperatorLog();
  $box->Load();
  $box->Render();
}

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
