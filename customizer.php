<?
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-login.php");
require_once("include_pouet/box-index-bbs-latest.php");
require_once("include_pouet/box-index-cdc.php");
require_once("include_pouet/box-index-watchlist.php");
require_once("include_pouet/box-index-latestadded.php");
require_once("include_pouet/box-index-latestreleased.php");
require_once("include_pouet/box-index-latestcomments.php");
require_once("include_pouet/box-index-latestparties.php");
require_once("include_pouet/box-index-upcomingparties.php");
require_once("include_pouet/box-index-topmonth.php");
require_once("include_pouet/box-index-topalltime.php");
require_once("include_pouet/box-index-news.php");
require_once("include_pouet/box-index-searchbox.php");
require_once("include_pouet/box-index-affilbutton.php");
require_once("include_pouet/box-index-stats.php");
require_once("include_pouet/box-index-user-topglops.php");
require_once("include_pouet/box-index-oneliner-latest.php");

class PouetBoxCustomizer extends PouetBox {
  function PouetBoxCustomizer() {
    parent::__construct();
    $this->uniqueID = "pouetbox_customizer";
    $this->title = "custom&ouml;olobstormaziabletic 7004+ super MK2!";
  }

  function LoadFromDB() 
  {
    global $currentUser;
    $customizerJSON = SQLLib::selectRow(sprintf_esc("select customizerJSON from usersettings where id = %d",$currentUser->id))->customizerJSON;
    $customizer = json_decode($customizerJSON,true);
    if (!$customizer["frontpage"])
    {
      require_once("include_pouet/default_usersettings.php");
      $customizer = json_decode($DEFAULT_USERSETTINGS->customizerJSON, true);
    }
    $this->boxes = $customizer["frontpage"];
  }

  function Commit( $data )
  {
    global $currentUser;

    $customizerJSON = get_setting("customizerJSON");
    $customizer = json_decode($customizerJSON,true);
    if ($data["up"])
    {
      $col = key($data["up"]);
      $boxIdx = key($data["up"][$col]);
      
      $pre    = array_slice( $this->boxes[$col], 0, $boxIdx - 1 );
      $swap   = $this->boxes[$col][$boxIdx-1];
      $selBox = $this->boxes[$col][$boxIdx];
      $post   = array_slice( $this->boxes[$col], $boxIdx + 1 );
      
      $this->boxes[$col] = array_merge($pre, array($selBox), array($swap), $post);
    }
    else if ($data["down"])
    {
      $col = key($data["down"]);
      $boxIdx = key($data["down"][$col]);
      
      $pre    = array_slice( $this->boxes[$col], 0, $boxIdx );
      $selBox = $this->boxes[$col][$boxIdx];
      $swap   = $this->boxes[$col][$boxIdx + 1];
      $post   = array_slice( $this->boxes[$col], $boxIdx + 2 );
      
      $this->boxes[$col] = array_merge($pre, array($swap), array($selBox), $post);
    }
    else if ($data["left"])
    {
      $col = key($data["left"]);
      $boxIdx = key($data["left"][$col]);
      
      while (key($this->boxes) !== $col && key($this->boxes)) next($this->boxes);
      prev($this->boxes);
      
      $target = key($this->boxes);
      
      $selBox = $this->boxes[$col][$boxIdx];
      unset($this->boxes[$col][$boxIdx]);
      $this->boxes[$target][] = $selBox;
    }
    else if ($data["right"])
    {
      $col = key($data["right"]);
      $boxIdx = key($data["right"][$col]);
      
      while (key($this->boxes) !== $col && key($this->boxes)) next($this->boxes);
      next($this->boxes);
      
      $target = key($this->boxes);
      
      $selBox = $this->boxes[$col][$boxIdx];
      unset($this->boxes[$col][$boxIdx]);
      $this->boxes[$target][] = $selBox;
    }
    
    foreach($this->boxes as $bar=>&$boxlist)
      $boxlist = array_values($boxlist);
      
    $customizer["frontpage"] = $this->boxes;
    
    $json = json_encode($customizer);
    SQLLib::UpdateRow("usersettings",array("customizerJSON"=>$json),"id=".(int)$currentUser->id);
    $_SESSION["settings"]->customizerJSON = $json;
    
    return array();
  }
  
  function RenderContent() 
  {
    $x = 0;
    foreach($this->boxes as $bar=>$boxlist)
    {
      echo "  <div id='"._html($bar)."' class='column'>\n";
      $y = 0;
      foreach($boxlist as $box)
      {
        $class = "PouetBox".$box["box"];
        $p = new $class();
        
        echo "  <div class='customizerBox'>\n";  
        echo "    <h2>";
        echo _html($p->title);
        echo "<span class='controls'>";
        if ($y > 0)
          printf("  <input type='submit' name='up[%s][%d]' value='&#9650;'/>",_html($bar),$y);
        if ($y < count($boxlist) - 1)
          printf("  <input type='submit' name='down[%s][%d]' value='&#9660;'/>",_html($bar),$y);
        if ($x > 0)
          printf("  <input type='submit' name='left[%s][%d]' value='&#9664;'/>",_html($bar),$y);
        if ($x < count($this->boxes) - 1)
          printf("  <input type='submit' name='right[%s][%d]' value='&#9654;'/>",_html($bar),$y);
        echo "</span>";
        echo "</h2>\n";  
        echo "  </div>\n";
        
        $y++;
      }
      echo "  </div>\n";
      $x++;
    }
  }
/*  
  function RenderFooter()
  {
    echo "<div class='foot'/>";
    echo "  <input type='submit' value='Submit' />";
    echo "</div>";
  }
*/
};

$form = new PouetFormProcessor();
$form->SetSuccessURL("customizer.php",true);

$box = new PouetBoxCustomizer();
$form->Add( "customizer", $box );
$box->Load();

if ($currentUser)
  $form->Process();

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
