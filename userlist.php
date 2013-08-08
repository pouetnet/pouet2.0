<?
require_once("bootstrap.inc.php");

class PouetBoxUserlist extends PouetBox
{
  var $id;
  var $group;

  function PouetBoxUserlist() {
    parent::__construct();
    $this->uniqueID = "pouetbox_userlist";
  }

  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return _html("userlist.php?" . http_build_query($query));
  }
  function LoadFromDB() {
    $s = new SQLSelect();

    $perPage = get_setting("userlistusers");
    $this->page = (int)max( 1, (int)$_GET["page"] );

    $s = new BM_Query("users");
    //$s->AddWhere(sprintf_esc("(prods.group1 = %d) or (prods.group2 = %d) or (prods.group3 = %d)",$this->id,$this->id,$this->id));
    //$s->AddOrder("prods.date DESC, prods.quand DESC");

    $dir = "DESC";
    if ($_GET["reverse"])
      $dir = "ASC";
    switch($_GET["order"])
    {
      case "nickname": $s->AddOrder("users.nickname ".$dir); break;
      case "age": $s->AddOrder("users.quand ".$dir); break;
      case "level": $s->AddOrder("users.level ".$dir); break;
      case "glops":
      default: $s->AddOrder("users.glops ".$dir); break;
      //default: $s->AddOrder("prods.date DESC"); $s->AddOrder("prods.quand DESC"); break;
    }
    $s->AddOrder("users.id ".$dir);

    $s->SetLimit( $perPage, (int)(($this->page-1) * $perPage) );

    //echo $s->GetQuery();

    $this->users = $s->performWithCalcRows( $this->count );

    $this->maxglops = SQLLib::SelectRow("SELECT MAX(glops) as m FROM users")->m;
  }

  function Render() {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "nickname"=>"nickname",
      "age"=>"age",
      "level"=>"level",
      "glops"=>"glöps",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a href='%s' class='%s%s' id='%s'>%s</a></th>\n",
        $this->BuildURL(array("order"=>$key)),$_GET["order"]==$key?"selected":"",($_GET["order"]==$key && $_GET["reverse"])?" reverse":"","sort_".$key,$text);
      if ($key == "type" || $key == "name") $out = str_replace("</th>","",$out);
      if ($key == "platform" || $key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    foreach ($this->users as $p) {
      echo "<tr>\n";

      echo "<td>\n";
      echo $p->PrintLinkedAvatar()." ";
      echo $p->PrintLinkedName();
      echo "</td>\n";

      echo "<td class='date'>\n";
      echo dateDiffReadableDays(time(),$p->quand);
      echo "</td>\n";

      echo "<td>\n";
      echo $p->level;
      echo "</td>\n";

      $pop = (int)($p->glops * 100 / $this->maxglops);
      echo "<td><div class='innerbar_solo' style='width: ".$pop."px' title='".$p->glops." glöps'>&nbsp;<span>".$pop."%</span></div></td>\n";

      echo "</tr>\n";
    }

    $perPage = get_setting("userlistusers");

    echo "<tr>\n";
    echo "<td class='nav' colspan=".(count($headers)).">\n";

    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='".$this->BuildURL(array("page"=>($this->page - 1)))."'>previous page</a></div>\n";
    if ($this->page < ($this->count / $perPage))
      echo "  <div class='nextpage'><a href='".$this->BuildURL(array("page"=>($this->page + 1)))."'>next page</a></div>\n";

    echo "  <select name='page'>\n";
    for ($x=1; $x<=($this->count / $perPage) + 1; $x++)
      printf("    <option value='%d'%s>%d</option>\n",$x,$x==$this->page?" selected='selected'":"",$x);
    echo "  </select>\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
    return $s;
  }
};

///////////////////////////////////////////////////////////////////////////////

$p = new PouetBoxUserlist();
$p->Load();
$TITLE = "userlist";
if ($p->page > 1)
  $TITLE .= " :: page ".(int)$p->page;


require_once("include_pouet/header.php");
require_once("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo "<form action='userlist.php' method='get'>\n";

foreach($_GET as $k=>$v)
  if ($k != "type" && $k != "platform" && $k != "page")
    echo "<input type='hidden' name='"._html($k)."' value='"._html($v)."'/>\n";

if($p) $p->Render();
echo "</form>\n";
echo "</div>\n";

require_once("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
