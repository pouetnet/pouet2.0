<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");

if (!$currentUser || !$currentUser->IsGloperator())
{
  redirect("index.php");
  exit();
}

function pouetAdmin_recacheFrontPage()
{
  $content = "<ul>";
  foreach(glob("cache/*") as $v) { $content .= "<li>deleting '".$v."'</li>\n"; unlink($v); }
  $content .= "</ul>";
  return $content;
}

function pouetAdmin_recacheTopDemos()
{
  $content = "<ul>";

  $total = array();
  
  // this needs to be made faster. a LOT faster.
  $i=0;
  $query="SELECT id FROM prods ORDER BY views DESC";
  $result = SQLLib::Query($query);
  while($tmp = SQLLib::Fetch($result)) {
    $total[$tmp->id]+=$i;
    $i++;
  }
  $content .= "<li>".$i." prod views loaded</li>\n";

  //var_dump($total);
  
  $i=0;
  $query="SELECT prods.id,SUM(comments.rating) AS somme FROM prods JOIN comments ON prods.id=comments.which GROUP BY prods.id ORDER BY somme DESC";
  $result = SQLLib::Query($query);
  while($tmp = SQLLib::Fetch($result)) {
    $total[$tmp->id]+=$i;
    $i++;
  }
  $content .= "<li>".$i." vote counts loaded</li>\n";
  
  asort($total);
  
  $i=1;
  unset($tmp);
  unset($top_demos);
  while ((list ($key, $val)=each($total))) {
    $query="UPDATE prods SET rank=".$i." WHERE id=".$key;
    SQLLib::Query($query);
    $i++;
  }
  $content .= "<li>".$i." prod rankings updated</li>\n";

  $content .= "</ul>";

  unlink('cache/pouetbox_topalltime.cache');
  unlink('cache/pouetbox_topmonth.cache');
  return $content;
}

class PouetBoxAdmin extends PouetBox {
  function PouetBoxAdmin() {
    parent::__construct();
    $this->uniqueID = "pouetbox_admin";
    $this->title = "i'm gonna wreck it !";
  }

  function Render() 
  {
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    echo "<h2>i'm gonna wreck it !</h2>\n";
    echo "<ul class='boxlist'>\n";
    $actions = array(
      "recacheFrontPage" => "flush front page cache",
      "recacheTopDemos" => "recalculate top demo list",
    );
    foreach($actions as $k=>$v)
    {
      echo "  <li>";
      echo "<form method='post'>";

      $csrf = new CSRFProtect();
      $csrf->PrintToken();

      echo _html($v).": ";
      echo "<input name='".$k."' type='submit' value='submit'/>";
      echo "</form>";
      echo "</li>\n";
    }
    echo "</ul>\n";
    echo "</div>\n";
  }
};

$TITLE = "admin";

require_once("include_pouet/header.php");
require_once("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$content = "";
if ($_POST)
{
  $csrf = new CSRFProtect();
  if ($csrf->ValidateToken())
  {
    foreach($_POST as $k=>$v)
    {
      $func = "pouetAdmin_".$k;
      if (function_exists($func))
        $content .= $func();
    }
  }
}

if (!get_login_id())
{
  require_once("include_pouet/box-login.php");
  $box = new PouetBoxLogin();
  $box->Render();
}

if ($content)
{
  $msg = new PouetBoxModalMessage( true );
  $msg->classes[] = "successbox";
  $msg->title = "Success!";
  $msg->message = $content;
  $msg->Render();
}

$box = new PouetBoxAdmin();
$box->Render();

echo "</div>\n";

require_once("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
