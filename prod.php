<?
include_once("bootstrap.inc.php");
//include_once("include_pouet/box-prod-comments.php");
//include_once("include_pouet/box-prod-main.php");
//include_once("include_pouet/box-prod-popularityhelper.php");
//include_once("include_pouet/box-prod-submitchanges.php");
include_once("include_pouet/box-prod-post.php");

class PouetBoxProdMain extends PouetBox {
  var $id;
  var $data;
  var $prod;
  var $votes;
  
  var $maxviews;
  
  function PouetBoxProdMain($id) {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodmain";
    $this->id = (int)$id;
    //$this->title = "some stats";
  }

  function LoadFromDB() {
    $this->prod = PouetProd::spawn( $this->id );
    if(!$this->prod)
      return;
      
    if($this->prod->latestip != $_SERVER["REMOTE_ADDR"] && CheckReferrer($_SERVER["HTTP_REFERER"]) ) 
    {
      SQLLib::Query(sprintf_esc("UPDATE prods SET views=views+1, latestip='%s' WHERE id=%d",$_SERVER["REMOTE_ADDR"],$this->id)); 
    }
      
    $this->maxviews = SQLLib::SelectRow("SELECT MAX(views) as m FROM prods")->m;
    
    $a = array(&$this->prod);
    PouetCollectPlatforms( $a );

    if ($this->prod->boardID)
      $this->board = SQLLib::SelectRow(sprintf_esc("SELECT * FROM bbses WHERE id = %d",$this->prod->boardID));

    $s = new BM_Query();
    $s->AddField("added");
    $s->AddTable("screenshots");
    $s->SetLimit(1);
    $s->attach(array("screenshots"=>"user"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("prod=%d",$this->id));
    list($this->screenshot) = $s->perform();

    $s = new BM_Query();
    $s->AddField("prodotherparty.partycompo");
    $s->AddField("prodotherparty.party_place");
    $s->AddField("prodotherparty.party_year");
    $s->AddTable("prodotherparty");
    $s->attach(array("prodotherparty"=>"party"),array("parties as party"=>"id"));
    $s->AddWhere(sprintf_esc("prod=%d",$this->id));
    $rows = $s->perform();
    foreach($rows as $row)
    {
      $this->prod->placings[] = new PouetPlacing( array("party"=>$row->party,"compo"=>$row->partycompo,"ranking"=>$row->party_place,"year"=>$row->party_year) );
    }
    
    $s = new BM_Query();
    $s->AddTable("affiliatedprods");
    $s->AddField("affiliatedprods.type");
    $s->attach(array("affiliatedprods"=>"original"),array("prods as prodOriginal"=>"id"));
    $s->attach(array("affiliatedprods"=>"derivative"),array("prods as prodDerivative"=>"id"));
    $s->AddWhere(sprintf_esc("affiliatedprods.original=%d or affiliatedprods.derivative=%d",$this->id,$this->id));
    $this->relatedProds = $s->perform();

    $s = new BM_Query();
    $s->AddTable("users_cdcs");
    $s->attach(array("users_cdcs"=>"user"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("cdc = %d",$this->id));
    $cdcs = $s->perform();
    
    $this->userCDCs = array();
    foreach($cdcs as $v)
      $this->userCDCs[$v->user->id] = $v;
    $this->isPouetCDC = SQLLib::selectRow(sprintf_esc("select * from cdc where which = %d",$this->id));
    
    $this->awards = SQLLib::selectRows(sprintf_esc("select * from sceneorgrecommended where prodid = %d order by type, category",$this->id));
    
    $this->downloadLinks = SQLLib::selectRows(sprintf_esc("select * from downloadlinks where prod = %d",$this->id));
  }

  function RenderScreenshot() { 
    $shotpath = find_screenshot($this->prod->id);
    if ($shotpath)
    {
      $title = "screenshot added by "._html($this->screenshot->user->nickname)." on "._html($this->screenshot->added);
      return "<img src='".POUET_CONTENT_URL.$shotpath."' alt='".$title."' title='".$title."'/>\n";
    }
    else
    {
      global $currentUser;
      $s = "no screenshot yet.\n";
      if ($currentUser && $currentUser->CanSubmitItems())
      {
        $s .= sprintf("<br/>[<a class='submitadditional' href='submit_prod_info.php?which=%d'>submit one!</a>]",$this->prod->id);
      }
      return $s;
    }
  }
  
  function RenderDetails() 
  { 
    global $currentUser;
    //var_dump($this->data);
    echo "<table id='stattable'>\n";
    echo " <tr>\n";
    echo "  <td>platform :</td>\n";
    echo "  <td>".$this->prod->RenderPlatformNames()."</td>\n";
    echo " </tr>\n";
    echo " <tr>\n";
    echo "  <td>type :</td>\n";
    echo "  <td>".$this->prod->RenderTypeNames()."</td>\n";
    echo " </tr>\n";
    echo " <tr>\n";
    echo "  <td>release date :</td>\n";
    echo "  <td>";
    if ($this->prod->date && $this->prod->date{0}!="0")
    {
      echo $this->prod->RenderReleaseDate();
    }
    else
    {
      echo "<span class='na'>n/a</span>";
      if ($currentUser && $currentUser->CanSubmitItems())
      {
        printf(" [<a class='submitadditional' href='submit_prod_info.php?which=%d'>+</a>]",$this->prod->id);
      }
    }
    echo "</td>\n";
    echo " </tr>\n";
    if ($this->prod->party->id != NO_PARTY_ID)
    {
      if (count($this->prod->placings) == 1) 
      {
        $p = $this->prod->placings[0];
        if ($p->party)
        {
          echo " <tr>\n";
          echo "  <td>release party :</td>\n";
          echo "  <td>".$p->party->PrintLinked($p->year)."</td>\n";
          echo " </tr>\n";
        }
        echo " <tr>\n";
        echo "  <td>compo :</td>\n";
        echo "  <td>";
        if ($p->compo) 
        {
          echo $p->compo;
        }
        else {
          echo "<span class='na'>n/a</span>";
          if ($currentUser && $currentUser->CanSubmitItems())
          {
            printf(" [<a class='submitadditional' href='submit_prod_info.php?which=%d'>+</a>]",$this->prod->id);
          }
        }
        echo "</td>\n";
        echo " </tr>\n";
        echo " <tr>\n";
        echo "  <td>ranked :</td>\n";
        echo "  <td>";
        if ($p->ranking) 
        {
          echo $p->PrintRanking();
        }
        else {
          echo "<span class='na'>n/a</span>";
          if ($currentUser && $currentUser->CanSubmitItems())
          {
            printf(" [<a class='submitadditional' href='submit_prod_info.php?which=%d'>+</a>]",$this->prod->id);
          }
        }
        echo "</td>\n";
        echo " </tr>\n";
      }
    }
    if (count($this->relatedProds))
    {
      global $AFFILIATIONS_ORIGINAL;
      global $AFFILIATIONS_INVERSE;
      echo " <tr>\n";
      echo "  <td>related :</td>\n";
      echo "  <td id='relatedprods'>";
      echo "    <ul>";
      foreach($this->relatedProds as $r)
      {
        if ($r->prodOriginal->id == $this->id)
          printf("<li>%s: %s</li>",$AFFILIATIONS_ORIGINAL[$r->type],$r->prodDerivative ? $r->prodDerivative->RenderLink() : "" );
        else
          printf("<li>%s: %s</li>",$AFFILIATIONS_INVERSE[$r->type],$r->prodOriginal ? $r->prodOriginal->RenderLink() : "" );
      }
      echo "    </ul>";
      echo "</td>\n";
      echo " </tr>\n";
    }
    if ($this->prod->invitation)
    {
      $invitationParty = PouetParty::Spawn( $this->prod->invitation );
      global $AFFILIATIONS_ORIGINAL;
      global $AFFILIATIONS_INVERSE;
      echo " <tr>\n";
      echo "  <td>invitation for :</td>\n";
      echo "  <td>".$invitationParty->PrintLinked($this->prod->invitationyear)."</td>\n";
      echo " </tr>\n";
    }
    if ($this->board)
    {
      echo " <tr>\n";
      echo "  <td>advertising for :</td>\n";
      echo "  <td><a href='boards.php?which=".(int)$this->board->id."'>"._html($this->board->name)."</td>\n";
      echo " </tr>\n";
    }
    echo "</table>\n";
    
    if (count($this->prod->placings) > 1) {
      echo "<table id='partytable'>\n";
      echo " <tr>\n";
      echo "  <th>party</th>\n";
      echo "  <th>ranking</th>\n";
      echo "  <th>compo</th>\n";
      $n = 1;
      foreach ($this->prod->placings as $p) {
        if (!$p->party) continue;
        echo " <tr>\n";
        echo "  <td>".$p->party->PrintLinked($p->year)."</td>\n";
        //if ($p->ranking)
          echo "  <td>".$p->PrintRanking()."</td>\n";
        //if ($p->compo)
          echo "  <td>".$p->compo."</td>\n";
        echo " </tr>\n";
      }    
      echo "</table>\n";
    }
  }
  function RenderPopularity() { 
    $pop = (int)($this->prod->views * 100 / $this->maxviews);
    echo "popularity : ".$pop."%<br/>\n"; 
    echo "<div class='outerbar'><div class='innerbar' style='width: ".$pop."%'>&nbsp;<span>".$pop."%</span></div></div>\n";
    
    $year = substr($this->prod->date,0,4);
    foreach($this->awards as $award)
    {
    	printf("<a href='./sceneorg.php#%s'><img src='".POUET_CONTENT_URL."gfx/sceneorg/%s.gif' title='%s' alt='%s'/></a>",
        $award->type == "viewingtip" ? $year : $year . str_replace(" ","",$award->category),
        $award->type,
        $award->category,
        $award->category);
    }
  }
  function RenderAverage() { 
    $p = "isok";
    if ($this->prod->voteavg < 0) $p = "sucks";
    if ($this->prod->voteavg > 0) $p = "rulez";
    echo "<img src='".POUET_CONTENT_URL."gfx/".$p.".gif' alt='".$p."' />&nbsp;".sprintf("%.2f",$this->prod->voteavg)."\n";
    $cdcs = count($this->userCDCs);
    if ($this->isPouetCDC) $cdcs++;
    if ($cdcs)
    {
      echo "<img src='".POUET_CONTENT_URL."gfx/titles/coupdecoeur.gif' alt='cdcs' />&nbsp;".$cdcs."\n";
    }
    if ($this->prod->rank)
    {
      printf("<div id='alltimerank'>alltime top: #%d</div>",$this->prod->rank);
    }
  }
  function RenderThumbs() { 
    echo "<ul>\n";
    echo "<li><img src='".POUET_CONTENT_URL."gfx/rulez.gif' alt='rulez' />&nbsp;".$this->prod->voteup."</li>\n";
    echo "<li><img src='".POUET_CONTENT_URL."gfx/isok.gif'  alt='is ok' />&nbsp;".$this->prod->votepig."</li>\n";
    echo "<li><img src='".POUET_CONTENT_URL."gfx/sucks.gif' alt='sucks' />&nbsp;".$this->prod->votedown."</li>\n";
    echo "</ul>\n";
  }
  function RenderLinks() { 
    echo "<ul>\n";
    echo "<li>[<a id='mainDownloadLink' href='"._html($this->prod->download)."'>download</a>]</li>\n";
    foreach ($this->downloadLinks as $link)
    {
      echo "<li>[<a href='"._html($link->link)."'>"._html($link->type)."</a>]</li>\n";
    }
    echo "<li>[<a href='mirrors.php?which=".$this->id."'>mirrors...</a>]</li>\n";
    echo "</ul>\n";
  }

  function Render() 
  {
    global $currentUser;
    
    $timer[$this->uniqueID." render"]["start"] = microtime_float();
    
    echo "<table id='pouetbox_prodmain'>\n";
    echo "<tr>\n";
    echo "<th colspan='3'>\n";
    echo " <span id='title'><big>"._html($this->prod->name)."</big>";
    if ($this->prod->groups)
      echo " by ".$this->prod->RenderGroupsLong();
    echo "</span>\n";
    printf("<div id='nfo'>");
    if ($currentUser && $currentUser->CanEditItems())
    {
      printf("[<a href='admin_prod_edit.php?which=%d' class='adminlink'>edit</a>]\n",$this->id);
    }
    if (file_exists(get_local_nfo_path($this->id)))
    {
      printf("[<a href='prod_nfo.php?which=%d'>nfo</a>]\n",$this->id);
    }
    else if ($currentUser && $currentUser->CanSubmitItems())
    {
      printf(" <small>[<a class='submitadditional' href='submit_prod_info.php?which=%d'>+nfo</a>]</small>",$this->prod->id);
    }
    printf("</div>");
    echo "</th>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo " <td rowspan='3' id='screenshot'>".$this->RenderScreenshot()."</td>\n";
    echo " <td colspan='2'>\n";
    $this->RenderDetails();
    echo " </td>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo " <td class='r2'>\n";
    $this->RenderThumbs();
    echo " </td>\n";
    echo " <td id='popularity'>\n";
    $this->RenderPopularity();
    echo " </td>\n";
    echo "</tr>\n";
    
    echo "<tr>\n";
    echo " <td class='r2'>\n";
    $this->RenderAverage();
    echo " </td>\n";
    echo " <td id='links'>\n";
    $this->RenderLinks();
    echo " </td>\n";
    echo "</tr>\n";

    if($this->prod->addeduser)
    {
      echo "<tr>\n";
      echo " <td class='foot' colspan='3'>added on the ".$this->prod->quand." by ".$this->prod->addeduser->PrintLinkedName()." ".$this->prod->addeduser->PrintLinkedAvatar()."</td>\n";
      echo "</tr>\n";
    }
    
    echo "</table>\n";
    $timer[$this->uniqueID." render"]["end"] = microtime_float();
  }

};

class PouetBoxProdPopularityHelper extends PouetBox {
  var $data;
  var $prod;
  var $id;
  function PouetBoxProdPopularityHelper($id) {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodpopularityhelper";
    $this->title = "popularity helper";
    $this->id = $id;
  }

  function RenderContent() {
    echo "increase the popularity of this prod by spreading this URL:<br/>";
    echo "<input type='text' value='http://www.pouet.net/prod.php?which=".$this->id."' size='50' readonly='readonly' />";
  }
};

class PouetBoxProdComments extends PouetBox {
  var $id;
  var $topic;
  var $posts;
  function PouetBoxProdComments($id) {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodcomments";
    $this->title = "comments";
    $this->id = (int)$id;
  }

  function LoadFromDB() {
    $s = new BM_Query();
    $s->AddField("comments.id as id");
    $s->AddField("comments.comment as comment");
    $s->AddField("comments.rating as rating");
    $s->AddField("comments.quand as quand");
    $s->attach(array("comments"=>"who"),array("users as user"=>"id"));
    $s->AddTable("comments");
    $s->AddOrder("comments.quand");
    $s->AddWhere("comments.which=".$this->id);
    $this->perPage = get_setting("prodcomments");
    if (get_setting("prodcomments") != -1)
    {
      $sc = new SQLSelect();
      $sc->AddField("count(*) as c");
      $sc->AddWhere("comments.which=".$this->id);
      $sc->AddTable("comments");
      $this->commentCount = SQLLib::SelectRow($sc->GetQuery())->c;

      $this->numPages = (int)ceil($this->commentCount / $this->perPage);
      if (!isset($_GET["page"]))
        $this->page = $this->numPages;
      else
        $this->page = (int)$_GET["page"];

      $this->page = (int)max( $this->page, 1 );
      $this->page = (int)min( $this->page, $this->numPages );

      if ($this->numPages > 1)
        $s->SetLimit( $this->perPage, (int)(($this->page-1) * $this->perPage) );
    }
    $r = $s->perform();
    $this->data = $r;
  }

  function RenderNavbar() {
    echo "<div class='navbar'>\n";
    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='prod.php?which=".$this->id."&amp;page=".($this->page - 1)."'>previous page</a></div>\n";
    if ($this->page * $this->perPage < $this->commentCount)
      echo "  <div class='nextpage'><a href='prod.php?which=".$this->id."&amp;page=".($this->page + 1)."'>next page</a></div>\n";
    echo "  <div class='selector'>";
    echo "  <form action='prod.php' method='get'>\n";
    echo "   go to page <select name='page'>\n";
    
    for ($x = 1; $x <= $this->numPages; $x++)
      echo "      <option value='".$x."'".($x==$this->page?" selected='selected'":"").">".$x."</option>\n";
      
    echo "   </select> of ".$this->numPages."\n";
    echo "  <input type='hidden' name='which' value='".$this->id."'/>\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "  </form>\n";
    echo "  </div>\n";
    echo "</div>\n";
    return $s;
  }

  function RenderBody() 
  {
    global $main;
    foreach ($this->data as $c) 
    {
      $rating = $c->rating>0 ? "rulez" : ($c->rating<0 ? "sucks" : "");
      
      $p = $c->comment;
      $p = parse_message($p);

      echo "<div class='content cite-".$c->user->id."' id='c".$c->id."'>".$p."</div>\n";
      echo "<div class='foot'>\n";
      if ($c->rating)
        echo "<span class='vote ".$rating."'>".$rating."</span>";
      if ($main->userCDCs[$c->user->id])
      {
        echo "<span class='vote cdc'>cdc</span>";
        unset($main->userCDCs[$c->user->id]);
      }
    
      echo "added on the <a href='#c".$c->id."'>".$c->quand."</a> by ".
        $c->user->PrintLinkedName()." ".$c->user->PrintLinkedAvatar()."</div>\n";
    }
    if ($this->numPages > 1)
    {
      $this->RenderNavbar();
    }
  }
  
  function RenderFooter() {
    echo "</div>\n";
  }
};

class PouetBoxProdSubmitChanges extends PouetBox {
  var $data;
  var $prod;
  var $id;
  function PouetBoxProdSubmitChanges($id) {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodsubmitchanges";
    $this->title = "submit changes";
    $this->id = $id;
  }

  function RenderContent() {
    echo "<p>if this prod is a fake, some info is false or the download link is broken,</p>";
    echo "<p>do not post about it in the comments, it will get lost.</p>";
    //echo "instead, <a href='mailto:pouet@neuromatrice.net?subject=about%20prod%20number%20".$this->id."'>email</a> or <a href='topic.php?which=1024'>post</a> about it.";
    echo "<p>instead, <a href='topic.php?which=1024'>post</a> about it here ! [<a href='gloperator_log.php?which=".$this->id."&amp;what=prod'>previous edits</a>]</p>";
    //echo "<p>instead, <a href='submit_modification_request.php?prod=".$this->id."'>click here</a> !</p>";
  }

};

class PouetBoxProdSneakyCDCs extends PouetBox {
  var $data;
  var $prod;
  var $id;
  function PouetBoxProdSneakyCDCs() {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodsneakycdcs";
    $this->title = "sneaky cdcs";
  }

  function RenderBody() 
  {
    global $main;
    echo "<ul class='boxlist'>\n";
    foreach($main->userCDCs as $cdc)
    {
      echo "<li>".$cdc->user->PrintLinkedAvatar()." ".$cdc->user->PrintLinkedName()."</li>\n";
    }
    echo "</ul>\n";
  }

};


$prodid = (int)$_GET["which"];
if (!$prodid)
  $prodid = rand(1,20000);

$main = new PouetBoxProdMain($prodid);
$main->Load();
if ($main->prod)
  $TITLE = $main->prod->name.($main->prod->groups ? " by ".$main->prod->RenderGroupsPlain() : "");

include("include_pouet/header.php");
include("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo "  <div id='prodpagecontainer'>\n";

if ($main->prod)
{
  $main->Render();
  
  $p = new PouetBoxProdPopularityHelper($prodid);
  $p->Render();
  
  if (get_setting("prodcomments")!=0)
  {
    $p = new PouetBoxProdComments($prodid);
    $p->Load();
    if($p->data)
      $p->Render();
  }

  if ($main->userCDCs)
  {
    $p = new PouetBoxProdSneakyCDCs($prodid);
    $p->Render();
  }  
  
  $p = new PouetBoxProdSubmitChanges($prodid);
  $p->Render();
  
  $p = new PouetBoxProdPost($prodid);
  $p->Render();
}
else
{
  echo "something something prod not found in portuguese something";
}
echo "  </div>\n";
echo "</div>\n";

include("include_pouet/menu.inc.php");
include("include_pouet/footer.php");
?>
