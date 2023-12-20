<?php
require_once("bootstrap.inc.php");

class PouetBoxUserlist extends PouetBox
{
  public $id;
  public $group;
  public $users;
  public $maxglops;
  public $page;
  public $count;

  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_userlist";
  }

  function LoadFromDB()
  {
    $s = new SQLSelect();

    $perPage = get_setting("userlistusers");
    $this->page = (int)max( 1, (int)@$_GET["page"] );

    $s = new BM_Query("users");

    $dir = !@$_GET["reverse"];
    switch(@$_GET["order"])
    {
      case "nickname": $s->AddOrder("users.nickname ".($dir?"ASC":"DESC")); break;
      case "age": $s->AddOrder("users.registerDate ".($dir?"ASC":"DESC")); break;
      case "level": $s->AddOrder("users.level ".($dir?"ASC":"DESC")); break;
      case "glops":
      default: $s->AddOrder("users.glops ".($dir?"DESC":"ASC")); break;
    }
    $s->AddOrder("users.id ".($dir?"ASC":"DESC"));

    $s->SetLimit( $perPage, (int)(($this->page-1) * $perPage) );

    //echo $s->GetQuery();

    $this->users = $s->performWithCalcRows( $this->count );

    $this->maxglops = SQLLib::SelectRow("SELECT MAX(glops) as m FROM users")->m;
  }

  function Render()
  {
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
        adjust_query_header(array("order"=>$key)),@$_GET["order"]==$key?"selected":"",(@$_GET["order"]==$key && @$_GET["reverse"])?" reverse":"","sort_".$key,$text);
      if ($key == "type" || $key == "name") $out = str_replace("</th>","",$out);
      if ($key == "platform" || $key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    foreach ($this->users as $p)
    {
      echo "<tr>\n";

      echo "<td>\n";
      echo $p->PrintLinkedAvatar()." ";
      echo $p->PrintLinkedName();
      echo "</td>\n";

      echo "<td class='date'>\n";
      echo dateDiffReadableDays(time(),$p->registerDate);
      echo "</td>\n";

      echo "<td>\n";
      echo $p->level;
      echo "</td>\n";

      $pop = (int)($p->glops * 100 / $this->maxglops);
      echo "<td>".progress_bar_solo( $pop, $p->glops." glöps")."</td>\n";

      echo "</tr>\n";
    }

    $perPage = get_setting("userlistusers");

    echo "<tr>\n";
    echo "<td class='nav' colspan=".(count($headers)).">\n";

    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='".adjust_query( array("page"=>($this->page - 1)) )."'>previous page</a></div>\n";
    if ($this->page < ($this->count / $perPage))
      echo "  <div class='nextpage'><a href='".adjust_query( array("page"=>($this->page + 1)) )."'>next page</a></div>\n";

    echo "  <select name='page'>\n";
    for ($x=1; $x<=($this->count / $perPage) + 1; $x++)
      printf("    <option value='%d'%s>%d</option>\n",$x,$x==$this->page?" selected='selected'":"",$x);
    echo "  </select>\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
  }
};

///////////////////////////////////////////////////////////////////////////////

$p = new PouetBoxUserlist();
$p->Load();
$TITLE = "userlist";
if ($p->page > 1)
  $TITLE .= " :: page ".(int)$p->page;


require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo "<form action='userlist.php' method='get'>\n";

foreach($_GET as $k=>$v)
  if ($k != "type" && $k != "platform" && $k != "page")
    echo "<input type='hidden' name='"._html($k)."' value='"._html($v)."'/>\n";

if($p) $p->Render();
echo "</form>\n";
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
