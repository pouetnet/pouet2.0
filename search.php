<?
require_once("bootstrap.inc.php");

class PouetBoxSearchBoxMain extends PouetBox
{
  function PouetBoxSearchBoxMain() {
    parent::__construct();
    $this->uniqueID = "pouetbox_searchmain";
    $this->title = "search in pou&euml;t.net";
  }

  function RenderBody() {
    echo "<div class='content r1'>\n";
    echo "I'm looking for\n";
    echo "<input type='text' name='what' size='25' value=\""._html($_GET["what"])."\"/>\n";
    echo "and this is a [\n";

    $types = array("prod","group","party"/*,"board"*/,"user","bbs");
    $a = array();
    $selected = $_GET["type"] ? $_GET["type"] : "prod";
    foreach($types as $t)
      $a[] = "<input type='radio' name='type' value='".$t."' id='search".$t."' ".($t==$selected?" checked='checked'":"")." />&nbsp;<label for='search".$t."'>".$t."</label>\n";

    echo implode(" |\n",$a);

    echo "]</div>\n";
    echo "<div class='foot'><input type='submit' value='Submit' /></div>";
  }

};

class PouetBoxSearchProd extends PouetBox
{
  function PouetBoxSearchProd($terms = array()) {
    parent::__construct();
    $this->uniqueID = "pouetbox_searchprod";
    $this->terms = $terms;
  }

  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return _html("search.php?" . http_build_query($query));
  }
  function LoadFromDB() {
    $s = new SQLSelect();

    $perPage = get_setting("searchprods");
    $this->page = (int)max( 1, (int)$_GET["page"] );

    $s = new BM_Query("prods");
    $s->AddField("cmts.c as commentCount");
    $s->AddJoin("left","(select which, count(*) as c from comments group by which) as cmts","cmts.which = prods.id");
    $s->AddOrder("prods.name ASC");
    foreach($this->terms as $term)
      $s->AddWhere(sprintf_esc("prods.name LIKE '%%%s%%'",_like($term)));

    $s->SetLimit( $perPage, (int)(($this->page-1) * $perPage) );

    //var_dump($s->GetQuery());
    $this->prods = $s->performWithCalcRows( $this->count );

    PouetCollectPlatforms($this->prods);
    PouetCollectAwards($this->prods);

    $this->maxviews = SQLLib::SelectRow("SELECT MAX(views) as m FROM prods")->m;
  }

  function Render() {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "name"=>"name",
      "group"=>"group",
      "party"=>"release party",
      "release"=>"release date",
      "avg"=>"<img src='http://www.pouet.net/gfx/rulez.gif' alt='rulez' />".
             "<img src='http://www.pouet.net/gfx/isok.gif' alt='piggie' />".
             "<img src='http://www.pouet.net/gfx/sucks.gif' alt='sucks' />",
      "comments"=>"#",
      "views"=>"popularity",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      echo sprintf("<th>%s</th>\n",$text);
    }
    echo "</tr>\n";

    foreach ($this->prods as $p) {
      echo "<tr>\n";

      echo "<td>\n";
      echo $p->RenderTypeIcons();
      echo $p->RenderPlatformIcons();
      echo "<span class='prod'>".$p->RenderLink()."</span>\n";
      echo $p->RenderAwards();
      echo "</td>\n";

      echo "<td>\n";
      echo $p->RenderGroupsShortProdlist();
      echo "</td>\n";

      echo "<td>\n";
      if ($p->placings)
        echo $p->placings[0]->PrintResult($p->year);
      echo "</td>\n";

      echo "<td class='date'>".$p->RenderReleaseDate()."</td>\n";


      $i = "isok";
      if ($p->voteavg < 0) $i = "sucks";
      if ($p->voteavg > 0) $i = "rulez";
      echo "<td class='votes'>".sprintf("%.2f",$p->voteavg)."&nbsp;<img src='http://www.pouet.net/gfx/".$i.".gif' alt='".$i."' /></td>\n";

      echo "<td>".(int)$p->commentCount."</td>\n";

      $pop = (int)($p->views * 100 / $this->maxviews);
      echo "<td><div class='innerbar_solo' style='width: ".$pop."px'>&nbsp;<span>".$pop."%</span></div></td>\n";

      echo "</tr>\n";
    }

    $perPage = get_setting("searchprods");

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

class PouetBoxSearchGroup extends PouetBox
{
  function PouetBoxSearchGroup($terms = array()) {
    parent::__construct();
    $this->uniqueID = "pouetbox_searchgroup";
    $this->terms = $terms;
  }

  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return _html("search.php?" . http_build_query($query));
  }
  function LoadFromDB() {
    $s = new SQLSelect();

    $perPage = get_setting("searchprods");
    $this->page = (int)max( 1, (int)$_GET["page"] );

    $s = new BM_Query("groups");
    $s->AddField("p1.c as p1c");
    $s->AddField("p2.c as p2c");
    $s->AddField("p3.c as p3c");
    $s->AddJoin("left","(select group1, count(*) as c from prods group by group1) as p1","p1.group1 = groups.id");
    $s->AddJoin("left","(select group2, count(*) as c from prods group by group2) as p2","p2.group2 = groups.id");
    $s->AddJoin("left","(select group3, count(*) as c from prods group by group3) as p3","p3.group3 = groups.id");
    $s->AddOrder("groups.name ASC");
    foreach($this->terms as $term)
      $s->AddWhere(sprintf_esc("(groups.name LIKE '%%%s%%' or groups.acronym LIKE '%%%s%%')",_like($term),_like($term)));

    $s->SetLimit( $perPage, (int)(($this->page-1) * $perPage) );

    //var_dump($s->GetQuery());
    $this->groups = $s->performWithCalcRows( $this->count );

  }

  function Render() {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "group"=>"groups",
      "website"=>"websites",
      "prods"=>"prods",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      echo sprintf("<th>%s</th>\n",$text);
    }
    echo "</tr>\n";

    foreach ($this->groups as $g) {
      echo "<tr>\n";

      echo "<td class='name'>";
      echo $g->RenderLong();
      echo "</td>\n";

      echo "<td>";
      printf("<a href='%s'>%s</a>",$g->web,$g->web);
      echo "</td>\n";

      echo "<td>";
      echo $g->p1c + $g->p2c + $g->p3c;
      echo "</td>\n";

      echo "</tr>\n";
    }

    $perPage = get_setting("searchprods");

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

class PouetBoxSearchParty extends PouetBox
{
  function PouetBoxSearchParty($terms = array()) {
    parent::__construct();
    $this->uniqueID = "pouetbox_searchparty";
    $this->terms = $terms;
  }

  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return _html("search.php?" . http_build_query($query));
  }
  function LoadFromDB() {
    $s = new SQLSelect();

    $perPage = get_setting("searchprods");
    $this->page = (int)max( 1, (int)$_GET["page"] );

    $s = new BM_Query("parties");
    $s->AddField("p.c as prods");
    $s->AddJoin("left","(select party, count(*) as c from prods group by party) as p","p.party = parties.id");
    $s->AddOrder("parties.name ASC");
    foreach($this->terms as $term)
      $s->AddWhere(sprintf_esc("parties.name LIKE '%%%s%%'",_like($term)));

    $s->SetLimit( $perPage, (int)(($this->page-1) * $perPage) );

    //var_dump($s->GetQuery());
    $this->parties = $s->performWithCalcRows( $this->count );

  }

  function Render() {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "party"=>"party name",
      "website"=>"websites",
      "prods"=>"prods",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      echo sprintf("<th>%s</th>\n",$text);
    }
    echo "</tr>\n";

    foreach ($this->parties as $p) {
      echo "<tr>\n";

      echo "<td class='name'>";
      echo $p->PrintLinked();
      echo "</td>\n";

      echo "<td>";
      printf("<a href='%s'>%s</a>",$p->web,$p->web);
      echo "</td>\n";

      echo "<td>";
      echo (int)$p->prods;
      echo "</td>\n";

      echo "</tr>\n";
    }

    $perPage = get_setting("searchprods");

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

class PouetBoxSearchUser extends PouetBox
{
  function PouetBoxSearchUser($terms = array()) {
    parent::__construct();
    $this->uniqueID = "pouetbox_searchuser";
    $this->terms = $terms;
  }

  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return _html("search.php?" . http_build_query($query));
  }
  function LoadFromDB() {
    $s = new SQLSelect();

    $perPage = get_setting("searchprods");
    $this->page = (int)max( 1, (int)$_GET["page"] );

    $s = new BM_Query("users");
//    $s->AddField("p.c as prods");
//    $s->AddJoin("left","(select party, count(*) as c from prods group by party) as p","p.party = parties.id");
    $s->AddOrder("users.nickname ASC");
    foreach($this->terms as $term)
      $s->AddWhere(sprintf_esc("users.nickname LIKE '%%%s%%'",_like($term)));

    $s->SetLimit( $perPage, (int)(($this->page-1) * $perPage) );

    //var_dump($s->GetQuery());
    $this->users = $s->performWithCalcRows( $this->count );

  }

  function Render() {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "party"=>"nickname",
      "glops"=>"gl&ouml;ps",
      "reg"=>"registered",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      echo sprintf("<th>%s</th>\n",$text);
    }
    echo "</tr>\n";

    foreach ($this->users as $u) {
      echo "<tr>\n";

      echo "<td class='name'>";
      echo $u->PrintLinkedAvatar()." ";
      echo $u->PrintLinkedName();
      echo "</td>\n";

      echo "<td>";
      echo $u->glops;
      echo "</td>\n";

      echo "<td class='date'>";
      echo $u->quand;
      echo "</td>\n";

      echo "</tr>\n";
    }

    $perPage = get_setting("searchprods");

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


class PouetBoxSearchBBS extends PouetBox
{
  function PouetBoxSearchBBS($terms = array()) {
    parent::__construct();
    $this->uniqueID = "pouetbox_searchbbs";
    $this->terms = $terms;
  }

  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return _html("search.php?" . http_build_query($query));
  }
  function LoadFromDB() {
    $s = new SQLSelect();

    $perPage = get_setting("searchprods");
    $this->page = (int)max( 1, (int)$_GET["page"] );

    $s = new BM_Query("bbs_posts");
    $s->AddField("bbs_topics.topic as topic");
    $s->AddField("bbs_topics.id as topicID");
    $s->AddField("bbs_posts.id as postID");
    $s->AddField("bbs_posts.post as post");
    $s->AddField("bbs_posts.added as postDate");
    $s->AddJoin("left","bbs_topics","bbs_posts.topic = bbs_topics.id");
    $s->attach(array("bbs_posts"=>"author"),array("users as user"=>"id"));
    $s->AddOrder("bbs_posts.added DESC");
    foreach($this->terms as $term)
      $s->AddWhere(sprintf_esc("(bbs_posts.post LIKE '%%%s%%'or bbs_topics.topic LIKE '%%%s%%')",_like($term),_like($term)));

    $s->SetLimit( $perPage, (int)(($this->page-1) * $perPage) );

    $this->posts = $s->performWithCalcRows( $this->count );

  }

  function Render() {
    echo "<table id='".$this->uniqueID."' class='boxtable pagedtable'>\n";
    $headers = array(
      "topic"=>"topic",
      "lastpost"=>"post date",
      "user"=>"posted by",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      echo sprintf("<th>%s</th>\n",$text);
    }
    echo "</tr>\n";

    foreach ($this->posts as $p) {
      echo "<tr class='r2'>\n";

      echo "<td class='name'>";

      $s = _html($p->topic);
      foreach ($this->terms as $v2) {
        $v2 = preg_quote($v2,"/");
        $s = preg_replace("/(".$v2.")/i","<span class='searchhighlight'>$1</span>",$s);
      }
      echo "<a href='topic.php?post=".$p->postID."'>".$s."</a>";
      echo "</td>\n";
      echo "<td class='date'>";
      echo $p->postDate;
      echo "</td>\n";
      echo "<td>";
      echo $p->user->PrintLinkedAvatar()." ";
      echo $p->user->PrintLinkedName();
      echo "</td>\n";

      echo "</tr>\n";

      $s = strip_tags($p->post);
      $s = preg_replace("/(\s+)/"," ",$s);
      $s = _html(mb_strcut($s,max(0,stripos($s,$this->terms[0])-50),100,"utf-8"));
      foreach ($this->terms as $v2) {
        $v2 = preg_quote($v2,"/");
        $s = preg_replace("/(".$v2.")/i","<span class='searchhighlight'>$1</span>",$s);
      }

      echo "<tr class='r1'>\n";
      echo "  <td colspan='3'>...".$s."...</td>\n";
      echo "</tr>\n";
    }

    $perPage = get_setting("searchprods");

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

$TITLE = "search";
if ($_GET["what"])
  $TITLE .= ": ".$_GET["what"];

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

$p = new PouetBoxSearchBoxMain();
echo "<div id='content'>\n";

echo "<form action='search.php' method='get'>\n";
if($p) $p->Render();
echo "</form>\n";

echo "<form action='search.php' method='get'>\n";
foreach($_GET as $k=>$v)
  if ($k != "page")
    echo "<input type='hidden' name='"._html($k)."' value='"._html($v)."'/>\n";
if ($_GET["what"] && $_GET["type"])
{
  $terms = split_search_terms( $_GET["what"] );

  switch($_GET["type"])
  {
    case "bbs":
      $p = new PouetBoxSearchBBS($terms);
      $p->Load();
      $p->Render();
      break;
    case "user":
      $p = new PouetBoxSearchUser($terms);
      $p->Load();
      $p->Render();
      break;
    case "party":
      $p = new PouetBoxSearchParty($terms);
      $p->Load();
      $p->Render();
      break;
    case "group":
      $p = new PouetBoxSearchGroup($terms);
      $p->Load();
      $p->Render();
      break;
    default:
      $p = new PouetBoxSearchProd($terms);
      $p->Load();
      $p->Render();
      break;
  }
}
echo "</form>\n";
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
