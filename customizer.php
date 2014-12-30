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

  use PouetForm;
  function Commit( $data )
  {
    global $currentUser;

    $this->LoadFromDB();
    if ($data["parameter"])
    {
      foreach($data["parameter"] as $col=>$boxen)
        foreach($boxen as $boxIdx=>$box)
          foreach($box as $parameterName=>$value)
          {
            $_box = &$this->boxes[$col][$boxIdx];
            $class = "PouetBox".$_box["box"];
            $p = new $class();
            if (has_trait($p,"PouetFrontPage"))
            {
              $params = $p->GetParameterSettings();
              if (isset($params[$parameterName]["max"]))
                $value = min($value,$params[$parameterName]["max"]);
              if (isset($params[$parameterName]["min"]))
                $value = max($value,$params[$parameterName]["min"]);
            }
            $_box[$parameterName] = $value;
          }
    }
    if ($data["addBox"])
    {
      $col = key($this->boxes);
      if (class_exists( "PouetBox".$_POST["newBox"] ))
        $this->boxes[$col][] = array("box"=>$_POST["newBox"]);
    }
    if ($data["delete"])
    {
      $col = key($data["delete"]);
      $boxIdx = key($data["delete"][$col]);
      
      $pre    = array_slice( $this->boxes[$col], 0, $boxIdx );
      $post   = array_slice( $this->boxes[$col], $boxIdx + 1 );
      
      $this->boxes[$col] = array_merge($pre, $post);
    }
    else if ($data["up"])
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

    if (SQLLib::SelectRow(sprintf_esc("select id from usersettings where id=%d",(int)$currentUser->id)))
      SQLLib::UpdateRow("usersettings",array("customizerJSON"=>$json),"id=".(int)$currentUser->id);
    else
      SQLLib::InsertRow("usersettings",array("customizerJSON"=>$json,"id"=>(int)$currentUser->id));
    $_SESSION["settings"]->customizerJSON = $json;
    
    return array();
  }
  
  function RenderBody()
  {
    echo " <div class='content addnew'>\n";
    echo "add new box: ";
    echo "<select name='newBox'>";
    printf("<option value=''>---</option>\n",$v,_html($p->title));
    $availableBoxes = array(
      "Login",
      "CDC",
      "LatestAdded",
      "LatestReleased",
      "TopMonth",
      "TopAlltime",
      "LatestOneliner",
      "LatestBBS",
      "NewsBoxes",
      "SearchBox",
      "Stats",
      "AffilButton",
      "LatestComments",
      "Watchlist",
      "LatestParties",
      "UpcomingParties",
      "TopGlops",
    );
    foreach($availableBoxes as $v)
    {
      $class = "PouetBox".$v;
      $p = new $class();
      printf("<option value='%s'>%s</option>\n",$v,_html($p->title));
    }
    echo "</select>";
    echo "  <input type='submit' name='addBox' value='Submit' />";
    echo " </div>\n";
    echo " <div class='content'>\n";
    $x = 0;
    foreach($this->boxes as $bar=>$boxlist)
    {
      echo "  <div id='"._html($bar)."' class='column'>\n";
      $y = 0;
      foreach($boxlist as $box)
      {
        $class = "PouetBox".$box["box"];
        $p = new $class();
        
        echo "  <div class='customizerBox' data-class='"._html($box["box"])."'>\n";  
        echo "    <h2>";
        echo _html($p->title);
        echo "<span class='controls'>";
        if ($y > 0)
          printf("  <input type='submit' class='move' name='up[%s][%d]' value='&#9650;'/>",_html($bar),$y);
        if ($y < count($boxlist) - 1)
          printf("  <input type='submit' class='move' name='down[%s][%d]' value='&#9660;'/>",_html($bar),$y);
        if ($x > 0)
          printf("  <input type='submit' class='move' name='left[%s][%d]' value='&#9664;'/>",_html($bar),$y);
        if ($x < count($this->boxes) - 1)
          printf("  <input type='submit' class='move' name='right[%s][%d]' value='&#9654;'/>",_html($bar),$y);
        printf("  <input type='submit' name='delete[%s][%d]' value='X' title='remove box'/>",_html($bar),$y);
        echo "</span>";
        echo "    </h2>\n";  
        if (has_trait($p,"PouetFrontPage"))
        {
          $params = $p->GetParameterSettings();
          if ($params)
          {
            echo "    <div class='content r2'>\n";
            echo "      <div class='formifier'>\n";
            foreach($params as $name=>$values)
            {
              echo "        <div class='row'>\n";
              printf("        <label>%s:</label>\n",_html($values["name"]));
              printf("        <input type='number' name='parameter[%s][%d][%s]' value='%d'>\n",_html($bar),$y,_html($name),_html($box[$name]));
              echo "        </div>\n";
            }
            echo "      </div>\n";
            echo "    </div>\n";
          }
        }
        echo "  </div>\n";
        
        $y++;
      }
      echo "  </div>\n";
      $x++;
    }
    echo " </div>\n";
  }
  function RenderFooter()
  {
    echo "<div class='foot'/>";
    echo "  <input type='submit' value='Submit' />";
    echo "</div>";
  }
};

$form = new PouetFormProcessor();
$form->SetSuccessURL("customizer.php",true);

$box = new PouetBoxCustomizer();
$form->Add( "customizer", $box );

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
