<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-modalmessage.php");
require_once("include_pouet/box-login.php");

require_once("include_pouet_index/index_bootstrap.inc.php");

class PouetBoxCustomizer extends PouetBox 
{
  public $boxes;
  function __construct() 
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_customizer";
    $this->title = "custom\xC3\xB6olobstormaziabletic 7004+ super MK2!";
  }

  function LoadFromDB() 
  {
    global $currentUser;
    $row = SQLLib::selectRow(sprintf_esc("select customizerJSON from usersettings where id = %d",$currentUser->id));
	$customizerJSON = $row ? $row->customizerJSON : null;
    $customizer = $customizerJSON ? json_decode($customizerJSON,true) : array();
    if (!@$customizer["frontpage"])
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
    if ($data["jsonBoxData"])
    {
      // potential TODO: validate if data isn't bogus
      // (is it necessary? if the user breaks their own front page,
      // it's their own damn fault)
      $d = json_decode( $data["jsonBoxData"], true );
      if ($d !== null)
        $this->boxes = $d;
    }
    else
    {
      if ($data["parameter"])
      {
        foreach($data["parameter"] as $col=>$boxen)
        {
          foreach($boxen as $boxIdx=>$box)
          {
            $_box = &$this->boxes[$col][$boxIdx];
            $class = "PouetBoxIndex".$_box["box"];
            $p = new $class();
            if (has_trait($p,"PouetFrontPage"))
            {
              $params = $p->GetParameterSettings();
              foreach($params as $parameterName=>$paramValues)
              {
                $value = $data["parameter"][$col][$boxIdx][$parameterName];
                switch($paramValues["type"])
                {
                  case "checkbox":
                    $value = ($value == "on");
                    break;
                  default:
                    if (isset($paramValues["max"])) $value = min($value,$paramValues["max"]);
                    //if (isset($data["min"])) 
                    $value = max($value,0);
                    break;
                }
                $_box[$parameterName] = $value;
              }
            }
          }
        }
      }
      if ($data["addBox"])
      {
        $col = key($this->boxes);
        if (class_exists( "PouetBoxIndex".$_POST["newBox"] ))
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
    }
    foreach($this->boxes as $bar=>&$boxlist)
      $boxlist = array_values($boxlist);
      
    $customizer["frontpage"] = $this->boxes;

    $json = json_encode($customizer);
    
    global $currentUser;
    global $currentUserSettings;
    global $ephemeralStorage;
    
    if (SQLLib::SelectRow(sprintf_esc("select id from usersettings where id=%d",(int)$currentUser->id)))
      SQLLib::UpdateRow("usersettings",array("customizerJSON"=>$json),"id=".(int)$currentUser->id);
    else
      SQLLib::InsertRow("usersettings",array("customizerJSON"=>$json,"id"=>(int)$currentUser->id));
    $currentUserSettings->customizerJSON = $json;
    
    $ephemeralStorage->set( "settings:".$currentUser->id, $currentUserSettings );
    
    return array();
  }
  
  function RenderBody()
  {
    echo " <div class='content addnew'>\n";
    echo "add new box: ";
    echo "<select name='newBox'>";
    printf("<option value=''>---</option>\n");
    global $indexAvailableBoxes;
    foreach($indexAvailableBoxes as $v)
    {
      $class = "PouetBoxIndex".$v;
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
        $class = "PouetBoxIndex".$box["box"];
        if (!class_exists($class))
          continue;
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
        printf("  <input type='submit' class='close' name='delete[%s][%d]' value='X' title='remove box'/>",_html($bar),$y);
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
              switch(@$values["type"])
              {
                case "checkbox":
                  $value = isset($box[$name]) ? $box[$name] : @$values["default"];
                  printf("        <input type='checkbox' name='parameter[%s][%d][%s]' data-paramname='%s' %s>\n",_html($bar),$y,_html($name),_html($name),$value ? " checked='checked'" : "");
                  break;
                default:
                  printf("        <input type='number' name='parameter[%s][%d][%s]' data-paramname='%s'",_html($bar),$y,_html($name),_html($name));
                  //if ($values["min"]) 
                  printf(" min='%d'\n",$values["min"]);
                  if ($values["max"]) printf(" max='%d'\n",$values["max"]);
                  printf(" value='%d'>\n",_html( isset($box[$name]) ? $box[$name] : $values["default"]) );
                  break;
              }
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
?>
<script>
<!--
function isMouseOverElement( el, x, y )
{
  el = $(el);
  return (el.cumulativeOffset().left < x && x < el.cumulativeOffset().left + el.getDimensions().width
       && el.cumulativeOffset().top  < y && y < el.cumulativeOffset().top + el.getLayout().get("margin-box-height"));
}
function getTargetLocation(x,y)
{
  var targetColumn = null;
  var targetPosition = null;
  $$(".column").each(function(col){
    if ( isMouseOverElement(col,x,y) )
    {
      targetColumn = col;
      targetPosition = col.select(".customizerBox").length;
      var n = 0;
      col.select(".customizerBox").each(function(box){
        if ( isMouseOverElement(box,x,y) )
          targetPosition = n;
        n++;
      })
    }
  });
  return { column: targetColumn, position: targetPosition };
}

var originalColumn = null;
var originalPosition = 0;
document.observe("dom:loaded",function(){
  $("pouetbox_customizer").addClassName("js");
  $$("#pouetbox_customizer .customizerBox").each(function(item){
    item.select(".move").each(function(i) { i.hide(); } );
    item.down(".close").observe("click",function(ev){
      ev.stop();
      item.remove();
      Event.observe(window, 'beforeunload', function(e) {
        e.returnValue = 'are you sure you want to leave without saving the order ?';
      });          
    });
    item.down("h2").setStyle({"cursor":"move"});
    item.down("h2").observe("mousedown",function(ev){
      if (ev.isRightClick()) return;
      if (ev.findElement(".close")) return;
      ev.stop();
      item.addClassName("floaty");
      item.setStyle({
        "left":(ev.pointerX() - 120) + "px",
        "top" :(ev.pointerY() -  12) + "px"
      });
      originalColumn = item.up(".column");
      originalPosition = originalColumn.childElements().indexOf(item);
      document.body.insert(item);
    });
    item.down("h2").observe("mouseup",function(ev){
      if (ev.isRightClick()) return;
      if (ev.findElement(".close")) return;
      ev.stop();
      item.removeClassName("floaty");
      $$(".placeholder").invoke("remove");
      var targetColumn = originalColumn;
      var targetPosition = originalPosition;
      var t = getTargetLocation( ev.pointerX(), ev.pointerY() );
      if (t.column !== null) targetColumn = t.column;
      if (t.position !== null) targetPosition = t.position;
      var c = targetColumn.childElements();
      if (targetPosition >= c.length)
        targetColumn.insert({"bottom":item});
      else
        c[targetPosition].insert({"before":item});
    });    
  });
  document.observe("mousemove",function(ev){
    var el = document.body.down(".floaty");
    if (el)
    {
      el.setStyle({
        "left":(ev.pointerX() - 120) + "px",
        "top" :(ev.pointerY() -  12) + "px"
      });
      $$(".placeholder").invoke("remove");
      var item = new Element("div",{"class":"placeholder"});
      var t = getTargetLocation( ev.pointerX(), ev.pointerY() );
      var targetColumn = originalColumn;
      var targetPosition = originalPosition;
      if (t.column !== null) targetColumn = t.column;
      if (t.position !== null) targetPosition = t.position;
      if (targetColumn !== null && targetPosition !== null)
      {
        var c = targetColumn.childElements();
        if (targetPosition >= c.length)
          targetColumn.insert({"bottom":item});
        else
          c[targetPosition].insert({"before":item});
          
        Event.observe(window, 'beforeunload', function(e) {
          e.returnValue = 'are you sure you want to leave without saving the order ?';
        });          
      }
    }
  });
  $$("#pouetbox_customizer .foot input[type='submit']").first().observe("click",function(ev){
    var result = {};
    $$(".column").each(function(col){
      result[col.id] = [];
      col.select(".customizerBox").each(function(box){
        var o = {}
        o.box = box.getAttribute("data-class");
        box.select(".content input").each(function(inp){
          var v = Form.Element.getValue(inp);
          if (inp.type == 'checkbox') v = (v == "on");
          o[ inp.getAttribute("data-paramname") ] = v;
        });
        result[col.id].push(o);
      });
    });
    Event.stopObserving(window,'beforeunload');
    $$("#pouetbox_customizer .foot").first().insert( new Element("input",{type:"hidden",name:"jsonBoxData",value:JSON.stringify(result)}) );
//    ev.stop();
  });
});
//-->
</script>
<?php
  }
  function RenderFooter()
  {
    echo "  <div class='foot'/>";
    echo "    <input type='submit' value='Submit' />";
    echo "  </div>";
    echo "</div>";
  }
};

class PouetBoxCustomizerSitewide extends PouetBox
{
  public $namesNumeric;
  public $namesSwitch;
  public $formifier;
  public $fieldsSettings;
  function __construct( )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_customizersitewide";

    $this->title = "sitewide settings";
    
    $this->namesNumeric = array(
      // numbers
/*      
      "indextopglops" => "front page - top glops",
      "indextopprods" => "front page - top prods (recent)",
      "indextopkeops" => "front page - top prods (all-time)",
      "indexoneliner" => "front page - oneliner",
      "indexlatestadded" => "front page - latest added",
      "indexlatestreleased" => "front page - latest released",
      "indexojnews" => "front page - bitfellas news",
      "indexlatestcomments" => "front page - latest comments",
      "indexlatestparties" => "front page - latest parties",
      "indexbbstopics" => "front page - bbs topics",
      "indexwatchlist" => "front page - watchlist",
*/      
      "bbsbbstopics" => "bbs page - bbs topics",
      "prodlistprods" => "prodlist page - prods",
      "userlistusers" => "userlist page - users",
      "searchprods" => "search page - prods",
      "userlogos" => "user page - logos",
      "userprods" => "user page - prods",
      "usergroups" => "user page - groups",
      "userparties" => "user page - parties",
      "userscreenshots" => "user page - screenshots",
      "usernfos" => "user page - nfos",
      "usercomments" => "user page - comments",
      "userrulez" => "user page - rulez",
      "usersucks" => "user page - sucks",
      "commentshours" => "comments page - hours",
      "topicposts" => "topic page - posts",
    );
    $this->namesSwitch = array(
      //select
      "logos" => "logos",
      "topbar" => "top bar",
      "bottombar" => "bottom bar",
/*      
      "indexcdc" => "front page - cdc",
      "indexsearch" => "front page - search",
      "indexstats" => "front page - stats",
      "indexlinks" => "front page - links",
*/      
      "indexplatform" => "front page - show platform icons",
      "indextype" => "front page - show type icons",
      "indexwhoaddedprods" => "front page - who added prods",
//      "indexwhocommentedprods" => "front page - who commented prods",
      "topichidefakeuser" => "bbs page - hide fakeuser",
      "prodhidefakeuser" => "prod page - hide fakeuser",
      "displayimages" => "[img][/img] tags should be displayed as...",
//      "indexbbsnoresidue" => "residue threads on the front page are...",
    );    
    
    $this->formifier = new Formifier();
    
    global $currentUserSettings;
    $this->fieldsSettings = array();
    $a = array_merge($this->namesNumeric,$this->namesSwitch);
    foreach($a as $k=>$v)
    {
      $this->fieldsSettings[$k] = array();
      $this->fieldsSettings[$k]["value"] = $currentUserSettings ? $currentUserSettings->$k : $v;
      if (@$this->namesNumeric[$k])
      {
        $this->fieldsSettings[$k]["name"] = $this->namesNumeric[$k];
        $this->fieldsSettings[$k]["type"] = "number";
        $this->fieldsSettings[$k]["min"] = strpos($k,"index") === 0 ? 0 : 1;
        $this->fieldsSettings[$k]["max"] = POUET_CACHE_MAX;
      }
      if (@$this->namesSwitch[$k])
      {
        $this->fieldsSettings[$k]["name"] = $this->namesSwitch[$k];
        $this->fieldsSettings[$k]["type"] = "select";
        $this->fieldsSettings[$k]["assoc"] = true;
        $this->fieldsSettings[$k]["fields"] = array(0=>"hidden",1=>"displayed");
      }
    }
    // exceptions!
    $this->fieldsSettings["commentshours"]["max"] = 24 * 7;
    $this->fieldsSettings["topicposts"]["min"] = 1;
    //$this->fieldsSettings["indexojnews"]["max"] = 10;
    $this->fieldsSettings["displayimages"]["fields"] = array(0=>"links",1=>"images");
    //$this->fieldsSettings["indexbbsnoresidue"]["fields"] = array(0=>"shown",1=>"hidden");
    $this->fieldsSettings["prodcomments"]["name"] = "prod page - number of comments";
    $this->fieldsSettings["prodcomments"]["type"] = "select";
    $this->fieldsSettings["prodcomments"]["assoc"] = true;
    $this->fieldsSettings["prodcomments"]["fields"] = array(-1=>"all",0=>"hide",5=>"5",10=>"10",25=>"25",50=>"50",100=>"100");
    $this->fieldsSettings["prodcomments"]["value"] = $currentUserSettings ? $currentUserSettings->prodcomments : $DEFAULT_USERSETTINGS->prodcomments;


    if ($_POST)
    {
      foreach($_POST as $k=>$v)
      {
        if (@$this->fieldsSettings[$k]) $this->fieldsSettings[$k]["value"] = $v;
      }
    }    
  }
  function RenderFooter()
  {
    echo "  <div class='foot'/>";
    echo "    <input type='submit' value='Submit' />";
    echo "  </div>";
    echo "</div>";
  }
  use PouetForm;
  function Commit($data)
  {
    global $currentUser;
    global $currentUserSettings;
    global $ephemeralStorage;
    $sql = array();
    foreach ($this->fieldsSettings as $k=>$v)
    {
      if ($v["type"] == "number")
      {
        $sql[$k] = min($v["max"], max($v["min"], (int)($data[$k]) ));
      }
      else
      {
        $sql[$k] = (int)$data[$k];
      }
      $currentUserSettings->$k = (int)$sql[$k];
    }
    if (SQLLib::SelectRow(sprintf_esc("select id from usersettings where id = %d",(int)get_login_id())))
    {
      SQLLib::UpdateRow("usersettings",$sql,"id=".(int)get_login_id());
    }
    else
    {
      $sql["id"] = (int)get_login_id();
      SQLLib::InsertRow("usersettings",$sql);
    }
    $ephemeralStorage->set( "settings:".$currentUser->id, $currentUserSettings );
  }
  function RenderContent()
  {
    $this->formifier->RenderForm( $this->fieldsSettings );
  }
}


class PouetBoxCustomizerPanic extends PouetBox
{
  function __construct( )
  {
    parent::__construct();

    $this->uniqueID = "pouetbox_customizerpanic";

    $this->classes[] = "errorbox";

    $this->title = "panic button !";
  }
  use PouetForm;
  function Commit($data)
  {
    global $currentUser;
    global $currentUserSettings;
    global $ephemeralStorage;
    
    require_once("include_pouet/default_usersettings.php");
    $a = get_object_vars( $DEFAULT_USERSETTINGS );

    if (SQLLib::SelectRow(sprintf_esc("select id from usersettings where id=%d",(int)$currentUser->id)))
      SQLLib::UpdateRow("usersettings",$a,"id=".(int)$currentUser->id);
    else
      SQLLib::InsertRow("usersettings",array_merge(array("id"=>(int)$currentUser->id),$a) );
    $currentUserSettings = $DEFAULT_USERSETTINGS;
    $ephemeralStorage->set( "settings:".$currentUser->id, $currentUserSettings );

    return array();
  }
  function RenderContent()
  {
    echo "  <p>Click this button to reset your front page to 'factory default', in case something breaks !</p>";
    echo "  <input type='submit' value='reset front page settings !'/>";
    ?>
<script>
document.observe("dom:loaded",function(){
  $("pouetbox_customizerpanic").up("form").observe("submit",function(e){
    if (!confirm("are you sure you want to reset ?"))
      e.stop();
  });
});
</script>
    <?php
  }
}


$form = new PouetFormProcessor();
$form->SetSuccessURL("customizer.php",true);

$form->Add( "customizer", $box = new PouetBoxCustomizer() );
$form->Add( "customizersite", new PouetBoxCustomizerSitewide() );
$form->Add( "customizerpanic", new PouetBoxCustomizerPanic() );

$TITLE = $box->title;

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
