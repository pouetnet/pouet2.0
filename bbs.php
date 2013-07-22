<?
include_once("bootstrap.inc.php");
include_once("include_pouet/box-bbs-open.php");

class PouetBoxBBSTopicList extends PouetBox 
{
  var $id;
  var $group;
  
  function PouetBoxBBSTopicList() {
    parent::__construct();
    $this->uniqueID = "pouetbox_bbslist";
  }
  
  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return _html("bbs.php?" . http_build_query($query));
  }
  function LoadFromDB() {
    $s = new SQLSelect();

    $perPage = get_setting("bbsbbstopics");  
    $this->page = (int)max( 1, (int)$_GET["page"] );
    
    $s = new BM_query();
    $s->AddField("bbs_topics.id as id");
    $s->AddField("bbs_topics.lastpost as lastpost");
    $s->AddField("bbs_topics.firstpost as firstpost");
    $s->AddField("bbs_topics.topic as topic");
    $s->AddField("bbs_topics.count as count");
    $s->AddField("bbs_topics.category as category");
    $s->AddTable("bbs_topics");
    $s->attach(array("bbs_topics"=>"userfirstpost"),array("users as firstuser"=>"id"));    
    $s->attach(array("bbs_topics"=>"userlastpost"),array("users as lastuser"=>"id"));    
    

    $dir = "DESC";
    if ($_GET["reverse"])
      $dir = "ASC";

    switch($_GET["order"])
    {
      case "firstpost": $s->AddOrder("bbs_topics.firstpost ".$dir); break;
      case "lastpost": $s->AddOrder("bbs_topics.lastpost ".$dir); break;
      case "userfirstpost": $s->AddOrder("bbs_topics_firstuser.nickname ".$dir); break;
      case "userlastpost": $s->AddOrder("bbs_topics_lastuser.nickname ".$dir); break;
      case "topic": $s->AddOrder("bbs_topics.topic ".$dir); break;
      case "category": $s->AddOrder("bbs_topics.category ".$dir); break;
      case "count": $s->AddOrder("bbs_topics.count ".$dir); break;
      //default: $s->AddOrder("prods.date DESC"); $s->AddOrder("prods.quand DESC"); break;
    }
    $s->AddOrder("bbs_topics.lastpost ".$dir);
    $s->SetLimit( $perPage, (int)(($this->page - 1) * $perPage) );
    
    //echo $s->GetQuery();
    
    $this->topics = $s->performWithCalcRows( $this->count );
    //PouetCollectPlatforms($this->prods);

    //$this->maxtopics = SQLLib::SelectRow("SELECT MAX(views) as m FROM prods")->m;
  }

  function Render() {
    global $THREAD_CATEGORIES;
    
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
    echo "<tr><th colspan='".count($headers)."'><h2>the oldskool pouët.net bbs</h2></th></tr>\n";
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a href='%s' class='%s%s' id='%s'>%s</a></th>\n",
        $this->BuildURL(array("order"=>$key)),$_GET["order"]==$key?"selected":"",($_GET["order"]==$key && $_GET["reverse"])?" reverse":"","prodlistsort_".$key,$text); 
      if ($key == "type" || $key == "name") $out = str_replace("</th>","",$out);
      if ($key == "platform" || $key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    foreach ($this->topics as $p) {
      echo "<tr>\n";
      
      echo " <td>";
      echo $p->firstpost;
      echo "</td>\n";

      echo " <td>";
      echo $p->firstuser->PrintLinkedAvatar()." ";
      echo $p->firstuser->PrintLinkedName();
      echo "</td>\n";

      echo " <td>".$THREAD_CATEGORIES[$p->category]."</td>\n";
      
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
    
    $this->page = ((int)$_GET["page"] ? $_GET["page"] : 1);
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

$p = new PouetBoxBBSTopicList();
$p->Load();

$q = new PouetBoxBBSOpen();
$q->Load();

$TITLE = "BBS";
if ($p->page > 1)
  $TITLE .= " :: page ".(int)$p->page;


include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo "<form action='bbs.php' method='get'>\n";

foreach($_GET as $k=>$v)
  if ($k != "type" && $k != "platform" && $k != "page")
    echo "<input type='hidden' name='"._html($k)."' value='"._html($v)."'/>\n";

if($p) $p->Render();
echo "</form>\n";

if($q) $q->Render();

echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>