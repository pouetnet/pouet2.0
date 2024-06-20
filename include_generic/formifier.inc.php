<?php
class Formifier 
{
  public $canDeleteFiles;
  function __construct()
  {
    $this->canDeleteFiles = false;
  }
  function RenderForm( $fields )
  {
    $confirmFields = array();
    echo "  <div class='formifier'>\n";
    foreach($fields as $k=>$v)
    {
      if (@$v["type"]=="hidden")
      {
        echo "    <input type='hidden' name='"._html($k)."' id='"._html($k)."' value='"._html($v["value"])."'/>\n";
        continue;
      }
      echo "  <div class='row' id='row_".$k."'>\n";
      echo "    <label for='".$k."'>"._html(@$v["name"]?$v["name"]:$k).":</label>\n";
      switch (@$v["type"])
      {
        case "static":
          echo "    <div class='static' id='".$k."'>";
          echo (@$v["fields"]&&@$v["assoc"]?$v["fields"][$v["value"]]:$v["value"]);
          echo "</div>\n";
          break;
        case "statichidden":
          echo "    <div class='static' id='".$k."'>".(@$v["fields"]?$v["fields"][$v["value"]]:$v["value"])."</div>\n";
          echo "    <input type='hidden' name='".$k."' id='".$k."' value='".@$v["value"]."'/>\n";
          break;
        case "date":
          echo "    <input type='date' name='".$k."' id='".$k."' value='"._html(@$v["value"])."'/>\n";
          break;
        case "dateMonth":
          $year = $month = null;
          if (@$v["value"])
          {
            list($year,$month,) = sscanf($v["value"],"%d-%d-%d");
          }
          echo "    <div class='formdate' id='".$k."'>\n";
          echo "    <select name='".$k."_month' id='".$k."_month'>\n";
          echo "      <option></option>\n";
          for($x=1; $x<=12; $x++)
            echo "      <option value='".$x."'".($month==$x?" selected='selected'":"").">".date("F",mktime(0,0,0,$x,15))."</option>\n";
          echo "    </select>\n";
          echo "    <select name='".$k."_year' id='".$k."_year'>\n";
          echo "      <option></option>\n";
          for($x=date("Y"); $x>=POUET_EARLIEST_YEAR; $x--)
            echo "      <option".($year==$x?" selected='selected'":"").">".$x."</option>\n";
          echo "    </select>\n";
?>
<script>
document.observe("dom:loaded",function(){
  var i = new Element("a",{href:"#"}).update("this month");
  i.observe("click",function(ev){
    ev.stop();
    $("<?=$k?>_month").selectedIndex = new Date().getMonth() + 1;
    $("<?=$k?>_year").selectedIndex = 1;
  });
  $("<?=$k?>").insert(i);
  i.insert({"before":"(","after":")"});
});
</script>
<?php          
          echo "    </div>\n";
          break;
        case "avatar":
          echo "    <div id='avatarlist'>\n";
          echo "    <select name='".$k."' id='".$k."'>\n";
          global $avatars;
          if (!$v["value"])
            $v["value"] = basename( $avatars[ array_rand($avatars) ] );
          foreach($avatars as $path)
          {
            $f = basename($path);
            echo "      <option".($v["value"]==$f?" selected='selected'":"").">"._html($f)."</option>\n";
          }
          echo "    </select>\n";
          echo "    </div>\n";
          break;
        case "select":
          echo "    <select name='".$k.(@$v["multiple"]?"[]":"")."' id='".$k."'".(@$v["multiple"]?" multiple='multiple'":"").">\n";
          foreach($v["fields"] as $k=>$f)
          {
            $sel = "";
            if (isset($v["value"]))
            {
              $match = @$v["assoc"] ? $k : $f;
              if (@$v["multiple"])
                $sel = (array_search(@$v["assoc"]?$match:$f,$v["value"])!==false?" selected='selected'":"");
              else
                $sel = ($v["value"]==$match?" selected='selected'":"");
            }
            if (@$v["assoc"])
            {
                echo "      <option value='"._html($k)."' ".$sel.">"._html($f)."</option>\n";
            }
            else
            {
              echo "      <option".$sel.">"._html($f)."</option>\n";
            }
          }
          echo "    </select>\n";
          break;
        case "password":
          echo "    <input type='password' name='".$k."' id='".$k."' value='"."'/>\n";
          break;
        case "checkbox":
          echo "    <input type='checkbox' name='".$k."' id='".$k."' ".(@$v["value"]?" checked='checked'":"")."/>\n";
          break;
        case "file":
          echo "    <div>\n";
          echo "      <input type='file' name='".$k."' id='".$k."'".(@$v["accept"]?" accept='"._html($v["accept"])."'":"")."/>\n";
          if ($this->canDeleteFiles)
          {
            echo "      <input type='checkbox' name='".$k."_delete' id='".$k."_delete' /> <label for='".$k."_delete'>Delete file</label>\n";
            $confirmFields[] = $k."_delete";
          }
          echo "    </div>\n";
          break;
        case "number":
          echo "    <input type='number'";
          if (isset($v["min"]))
          {
            echo " min='"._html((int)$v["min"])."'";
            $v["value"] = max((int)$v["value"],(int)$v["min"]);
          }
          if (isset($v["max"]))
          {
            echo " max='"._html((int)$v["max"])."'";
            $v["value"] = min((int)$v["value"],(int)$v["max"]);
          }
          echo " name='".$k."' id='".$k."' value='"._html(@$v["value"])."'/>\n";
          break;
        case "url":
          echo "    <input type='url' name='".$k."' id='".$k."' ".(@$v["maxlength"]?"maxlength='".(int)$v["maxlength"]."' ":"")."value='"._html(@$v["value"])."'/>\n";
          break;
        case "email":
          echo "    <input type='email' name='".$k."' id='".$k."' ".(@$v["maxlength"]?"maxlength='".(int)$v["maxlength"]."' ":"")."value='"._html(@$v["value"])."'/>\n";
          break;
        case "textarea":
          echo "    <textarea name='".$k."' id='".$k."'>"._html(@$v["value"])."</textarea>\n";
          break;
        default:
          echo "    <input name='".$k."' id='".$k."'".(@$v["html"]?" ".$v["html"]:"")." ".(@$v["maxlength"]?"maxlength='".(int)$v["maxlength"]."' ":"")."value='"._html(@$v["value"])."'/>\n";
          break;
      }
      if (@$v["info"])
        echo "    <span>"._html($v["info"]).(@$v["required"]?" [<span class='req'>req</span>]":"")."</span>\n";
      echo "  </div>\n";
      if (@$v["infoAfter"])
        echo "    <p class='infoAfter'>".$v["infoAfter"]."</p>\n";
    }
    echo "  </div>\n";
    if ($confirmFields)
    {
?>
<script>
document.observe("dom:loaded",function(){
  var frm = $("<?=$confirmFields[0]?>").up("form");
  frm.observe("submit",function(ev){
    var b = false;
<?php
  foreach($confirmFields as $v)
  {
    printf("    if ($('%s').checked) b = true;\n",$v);
  }
?>
    if (!b)
      return;
    if (!confirm("are you sure you want to delete files ?"))
      ev.stop();
  });
});
</script>
<?php          
    }
  }
};
?>
