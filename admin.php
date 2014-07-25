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
  foreach(glob("cache/*") as $v) { $content .= "<li>deleting '".$v."'</li>\n"; @unlink($v); }
  $content .= "</ul>";
  return $content;
}

function pouetAdmin_recacheTopDemos()
{

  $total = array();

  // this needs to be made faster. a LOT faster.
  $i=0;
  $query="SELECT id,name,views FROM prods ORDER BY views DESC";
  $result = SQLLib::Query($query);
  $content = "<ol>";
  while($tmp = SQLLib::Fetch($result)) {
    $total[$tmp->id]+=$i;
    $i++;
    if ($i<=5)
      $content .= "<li><b>"._html($tmp->name)."</b> - ".$tmp->views." views</li>\n";
  }
  $content .= "</ol>";
  $content .= "<h3>".$i." prod views loaded</h3>\n";

  $i=0;
  // Get the list of prod IDs ordered by the sum of their comment ratings
  $sql = new SQLSelect();
  $sql->AddField("prods.id");
  $sql->AddField("prods.name");
  $sql->AddField("SUM(comments.rating) as theSum");
  $sql->AddTable("prods");
  $sql->AddJoin("","comments","prods.id = comments.which");
  $sql->AddGroup("prods.id");
  $sql->AddOrder("SUM(comments.rating) DESC");

  $result = SQLLib::Query( $sql->GetQuery() );
  $content .= "<ol>";
  while($tmp = SQLLib::Fetch($result)) {
    $total[$tmp->id]+=$i;
    $i++;
    if ($i<=5)
      $content .= "<li><b>"._html($tmp->name)."</b> - "._html($tmp->theSum)." votes</li>\n";
  }
  $content .= "</ol>";
  $content .= "<h3>".$i." vote counts loaded</h3>\n";

  asort($total);

  $i=1;
  unset($tmp);
  unset($top_demos);
  while ((list ($key, $val)=each($total))) {
    $query="UPDATE prods SET rank=".$i." WHERE id=".$key;
    SQLLib::Query($query);
    $i++;
  }
  $content .= "<h3>".$i." prod rankings updated</h3>\n";

  @unlink('cache/pouetbox_topalltime.cache');
  @unlink('cache/pouetbox_topmonth.cache');
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
    echo "  <li><a href='admin_modification_requests.php'>process modification requests</a></li>";
    if ($currentUser->IsModerator())
    {
      echo "  <li><a href='admin_faq.php'>edit faq items</a></li>";
    }
    echo "</ul>\n";
    echo "</div>\n";
  }
};

$TITLE = "admin";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

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

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
