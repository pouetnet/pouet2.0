<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-bbs-open.php");
require_once("include_pouet/box-login.php");

class PouetBoxBBSTopicList extends PouetBox
{
  public $id;
  public $group;
  public $categories;
  public $page;
  public $count;
  public $topics;

  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_bbslist";

    $row = SQLLib::selectRow("DESC bbs_topics category");
    $this->categories = enum2array($row->Type);
  }

  function LoadFromDB()
  {
    $s = new SQLSelect();

    $perPage = get_setting("bbsbbstopics");
    $this->page = (int)max( 1, (int)@$_GET["page"] );

    $s = new BM_query();
    $s->AddField("bbs_topics.id as id");
    $s->AddField("bbs_topics.lastpost as lastpost");
    $s->AddField("bbs_topics.firstpost as firstpost");
    $s->AddField("bbs_topics.topic as topic");
    $s->AddField("bbs_topics.count as count");
    $s->AddField("bbs_topics.category as category");
    $s->AddField("bbs_topics.closed as closed");
    $s->AddTable("bbs_topics");
    $s->attach(array("bbs_topics"=>"userfirstpost"),array("users as firstuser"=>"id"));
    $s->attach(array("bbs_topics"=>"userlastpost"),array("users as lastuser"=>"id"));


    $dir = "DESC";
    if (@$_GET["reverse"])
      $dir = "ASC";

    switch(@$_GET["order"])
    {
      case "firstpost": $s->AddOrder("bbs_topics.firstpost ".$dir); break;
      case "userfirstpost": $s->AddOrder("bbs_topics_firstuser.nickname ".$dir); break;
      case "userlastpost": $s->AddOrder("bbs_topics_lastuser.nickname ".$dir); break;
      case "topic": $s->AddOrder("bbs_topics.topic ".$dir); break;
      case "category": $s->AddOrder("bbs_topics.category ".$dir); break;
      case "count": $s->AddOrder("bbs_topics.count ".$dir); break;
      case "lastpost":
      default: $s->AddOrder("bbs_topics.lastpost ".$dir); break;
    }
    $s->AddOrder("bbs_topics.lastpost ".$dir);
    $s->SetLimit( $perPage, (int)(($this->page - 1) * $perPage) );

    if (@$_GET["category"])
      $s->AddWhere(sprintf_esc("category='%s'",$_GET["category"]));
    //echo $s->GetQuery();

    $this->topics = $s->performWithCalcRows( $this->count );
    //PouetCollectPlatforms($this->prods);

    //$this->maxtopics = SQLLib::SelectRow("SELECT MAX(views) as m FROM prods")->m;
  }

  function Render()
  {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "firstpost"=>"started",
      "userfirstpost"=>"by",
      "category"=>"category",
      "topic"=>"bbs topic",
      "count"=>"replies",
      "lastpost"=>"last post",
      "userlastpost"=>"by",
    );
    echo "<caption>the oldskool pouët.net bbs</caption>\n";
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th id='th_%s'><a href='%s' class='%s%s' id='%s'>%s</a></th>\n",
        $key,adjust_query_header(array("order"=>$key)),@$_GET["order"]==$key?"selected":"",(@$_GET["order"]==$key && @$_GET["reverse"])?" reverse":"","prodlistsort_".$key,$text);
      if ($key == "type" || $key == "name") $out = str_replace("</th>","",$out);
      if ($key == "platform" || $key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    foreach ($this->topics as $p)
    {
      printf("<tr class='%s'>\n",$p->closed?"closed":"");

      echo " <td>";
      echo $p->firstpost;
      echo "</td>\n";

      echo " <td>";
      echo $p->firstuser->PrintLinkedAvatar()." ";
      echo $p->firstuser->PrintLinkedName();
      echo "</td>\n";

      echo " <td>"._html($p->category)."</td>\n";

      echo " <td class='topic'>";
      echo "<a href='topic.php?which=".(int)$p->id."'>"._html($p->topic)."</a>";
      echo "</td>\n";

      echo " <td>".$p->count."</td>\n";

      echo " <td title='"._html(dateDiffReadable(time(),$p->lastpost))." ago'>";
      echo $p->lastpost;
      echo "</td>\n";

      echo " <td>";
      echo $p->lastuser->PrintLinkedAvatar()." ";
      echo $p->lastuser->PrintLinkedName();
      echo "</td>\n";

      echo "</tr>\n";
    }

    $perPage = get_setting("bbsbbstopics");

    echo "<tr>\n";
    echo "<td class='nav' colspan=".(count($headers)).">\n";

    $this->page = ((int)@$_GET["page"] ? $_GET["page"] : 1);
    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='".adjust_query(array("page"=>($this->page - 1)))."'>previous page</a></div>\n";
    if ($this->page < ($this->count / $perPage))
      echo "  <div class='nextpage'><a href='".adjust_query(array("page"=>($this->page + 1)))."'>next page</a></div>\n";

    echo "  <select name='page'>\n";
    for ($x=1; $x<=($this->count / $perPage) + 1; $x++)
      printf("    <option value='%d'%s>%d</option>\n",$x,$x==$this->page?" selected='selected'":"",$x);
    echo "  </select>\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
?>
<script>
<!--
var threadCategories = $A([<?php
foreach($this->categories as $v) echo "'"._js($v)."',";
?>]);
document.observe("dom:loaded",function(){
  var sel = new Element("select",{"id":"categoryFilter"});
  $("th_category").insert(sel);

  var q = location.href.toQueryParams();

  sel.add( new Option("-- filter to","") );
  threadCategories.each(function(item){
    sel.add( new Option(item,item) );
    if (item == q["category"])
      sel.selectedIndex = sel.options.length - 1;
  });
  sel.observe("change",function(){
    if (sel.selectedIndex == 0)
      location.href = "bbs.php";
    else
      location.href = "bbs.php?category=" + sel.options[ sel.selectedIndex ].value;
  });
});
//-->
</script>
<?php
  }
};

///////////////////////////////////////////////////////////////////////////////

$list = new PouetBoxBBSTopicList();
$list->Load();

$openNew = null;
if ($currentUser)
{
  if ($currentUser->CanOpenNewBBSTopic())
  {
    $openNew = new PouetBoxBBSOpen();
    $openNew->Load();
  }
}
else
{
  $openNew = new PouetBoxLogin();
}

$TITLE = "BBS";
if ($list->page > 1)
  $TITLE .= " :: page ".(int)$list->page;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo "<form action='bbs.php' method='get'>\n";

foreach($_GET as $k=>$v)
  if ($k != "type" && $k != "platform" && $k != "page")
    echo "<input type='hidden' name='"._html($k)."' value='"._html($v)."'/>\n";

if($list) $list->Render();
echo "</form>\n";

if($openNew) $openNew->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
