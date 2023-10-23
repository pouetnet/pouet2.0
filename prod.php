<?php
require_once("bootstrap.inc.php");
require_once("include_pouet/box-login.php");

if (@$_GET["post"]) // setting-independent post lookup
{
  $prodID = SQLLib::SelectRow(sprintf_esc("select which from comments where id = %d",$_GET["post"]))->which;
  if ($prodID)
  {
    if (get_setting("prodcomments") <= 0)
    {
      redirect(sprintf("prod.php?which=%d#c%d",$prodID,$_GET["post"]));
    }
    else
    {
      $inner = sprintf_esc("select id, @rowID:=@rowID+1 as rowID from comments, (SELECT @rowID:=0) as init where which = %d",$prodID);
      $row = SQLLib::SelectRow(sprintf_esc("select * from (".$inner.") as t where id = %d",$_GET["post"]));
      redirect(sprintf("prod.php?which=%d&page=%d#c%d",$prodID,(int)(($row->rowID - 1) / get_setting("prodcomments")) + 1,$_GET["post"]));
    }
    exit();
  }
}

class PouetBoxProdMain extends PouetBox
{
  public $id;
  public $data;
  public $prod;
  public $votes;
  public $linkCheck;
  public $screenshot;
  public $relatedProds;
  public $userCDCs;
  public $isPouetCDC;
  public $credits;
  public $downloadLinks;
  public $screenshotPath;
  public $board;

  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodmain";
    $this->id = (int)$id;
    //$this->title = "some stats";
  }

  function LoadFromDB()
  {
    $this->prod = PouetProd::spawn( $this->id );
    if(!$this->prod)
      return;

    if($this->prod->latestip != $_SERVER["REMOTE_ADDR"] && CheckReferrer($_SERVER["HTTP_REFERER"]) )
    {
      SQLLib::Query(sprintf_esc("UPDATE prods SET views=views+1, latestip='%s' WHERE id=%d",$_SERVER["REMOTE_ADDR"],$this->id));
    }

    $this->linkCheck = SQLLib::SelectRow(sprintf_esc("SELECT * FROM prods_linkcheck where prodID = %d",$this->id));

    $a = array(&$this->prod);
    PouetCollectPlatforms( $a );
    PouetCollectAwards( $a );

    if ($this->prod->boardID)
      $this->board = SQLLib::SelectRow(sprintf_esc("SELECT * FROM boards WHERE id = %d",$this->prod->boardID));

    $s = new BM_Query();
    $s->AddField("added");
    $s->AddTable("screenshots");
    $s->SetLimit(1);
    $s->attach(array("screenshots"=>"user"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("prod=%d",$this->id));
    $scr = $s->perform();
    $this->screenshot = $scr ? $scr[0] : null;

    $s = new BM_Query();
    $s->AddField("prodotherparty.party_compo");
    $s->AddField("prodotherparty.party_place");
    $s->AddField("prodotherparty.party_year");
    $s->AddTable("prodotherparty");
    $s->attach(array("prodotherparty"=>"party"),array("parties as party"=>"id"));
    $s->AddWhere(sprintf_esc("prod=%d",$this->id));
    $rows = $s->perform();
    foreach($rows as $row)
    {
      $this->prod->placings[] = new PouetPlacing( array("party"=>$row->party,"compo"=>$row->party_compo,"ranking"=>$row->party_place,"year"=>$row->party_year) );
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

    $s = new BM_Query();
    $s->AddTable("credits");
    $s->AddField("credits.role");
    $s->AddWhere(sprintf("credits.prodID = %d",$this->id));
    $s->Attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddOrder("credits.role");
    $this->credits = $s->perform();

    $this->downloadLinks = array();
    /*
    if ($this->prod->sceneorg)
    {
      $o = new stdClass();
      $o->type = "scene.org";
      $o->id = "sceneorgID";
      $o->link = "http://scene.org/file.php?id=".(int)$this->prod->sceneorg;
      $this->downloadLinks[] = $o;
    }
    */
    if ($this->prod->csdb)
    {
      $o = new stdClass();
      $o->type = "csdb";
      $o->id = "csdbID";
      $o->link = "http://csdb.dk/release/?id=".(int)$this->prod->csdb;
      $this->downloadLinks[] = $o;
    }
    if ($this->prod->zxdemo)
    {
      $o = new stdClass();
      $o->type = "zxdemo";
      $o->id = "zxdemoID";
      $o->link = "http://zxdemo.org/item.php?id=".(int)$this->prod->zxdemo;
      $this->downloadLinks[] = $o;
    }
    if ($this->prod->demozoo)
    {
      $o = new stdClass();
      $o->type = "demozoo";
      $o->id = "demozooID";
      $o->link = "http://demozoo.org/productions/".(int)$this->prod->demozoo."/";
      $this->downloadLinks[] = $o;
    }
    $this->downloadLinks = array_merge($this->downloadLinks,SQLLib::selectRows(sprintf_esc("select type, link from downloadlinks where prod = %d order by type",$this->id)));
    $this->screenshotPath = find_screenshot($this->prod->id);
  }

  function RenderScreenshot()
  {
    if ($this->screenshot && $this->screenshotPath)
    {
      $title = "screenshot added by "._html($this->screenshot->user->nickname)." on "._html($this->screenshot->added);
      return "<img src='".POUET_CONTENT_URL.$this->screenshotPath."' alt='".$title."' title='".$title."'/>\n";
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
    if ($this->prod->releaseDate && $this->prod->releaseDate[0]!="0")
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
          global $COMPOTYPES;
          echo $COMPOTYPES[ $p->compo ];
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
      if ($invitationParty)
      {
        echo " <tr>\n";
        echo "  <td>invitation for :</td>\n";
        echo "  <td>".$invitationParty->PrintLinked($this->prod->invitationyear)."</td>\n";
        echo " </tr>\n";
      }
    }
    if ($this->board)
    {
      echo " <tr>\n";
      echo "  <td>advertising for :</td>\n";
      echo "  <td><a href='boards.php?which=".(int)$this->board->id."'>"._html($this->board->name)."</a></td>\n";
      echo " </tr>\n";
    }
    echo "</table>\n";

    if (count($this->prod->placings) > 1)
    {
      echo "<table id='partytable'>\n";
      echo " <tr>\n";
      echo "  <th>party</th>\n";
      echo "  <th>ranking</th>\n";
      echo "  <th>compo</th>\n";
      $n = 1;
      foreach ($this->prod->placings as $p)
      {
        if (!$p->party) continue;
        echo " <tr>\n";
        echo "  <td>".$p->party->PrintLinked($p->year)."</td>\n";
        //if ($p->ranking)
          echo "  <td>".$p->PrintRanking()."</td>\n";
        //if ($p->compo)
          global $COMPOTYPES;
          echo "  <td>".$COMPOTYPES[ $p->compo ]."</td>\n";
        echo " </tr>\n";
      }
      echo "</table>\n";
    }
  }
  function RenderPopularity()
  {
    $pop = (int)calculate_popularity( $this->prod->views );
    echo "popularity : ".$pop."%<br/>\n";
    echo progress_bar( $pop, $pop."%" );

    $this->prod->RenderAwards();
  }
  function RenderAverage()
  {
    $p = "isok";
    if ($this->prod->voteavg < 0) $p = "sucks";
    if ($this->prod->voteavg > 0) $p = "rulez";
    echo "<ul id='avgstats'>";
    echo "<li class=".$p.">".$this->prod->RenderAvgRaw()."</li>\n";
    $cdcs = count($this->userCDCs);
    if ($this->isPouetCDC) $cdcs++;
    if ($cdcs)
    {
      echo "<li class='cdc'>".$cdcs."</li>\n";
    }

    global $currentUser;
    if ($currentUser)
    {
      echo "<li class='watchlist'>";
      echo "<form action='prod.php?which=".$this->prod->id."' method='post' id='watchlistFrm'>";
      $csrf = new CSRFProtect();
      $csrf->PrintToken();

      $row = SQLLib::SelectRow(sprintf_esc("select * from watchlist where prodID = %d and userID = %d",$this->prod->id,$currentUser->id));
      if ($row)
      {
        echo "<input type='hidden' name='wlAction' value='removeFromWatchlist'>";
        echo "<input type='submit' value='remove from watchlist' class='remove'/>";
      }
      else
      {
        echo "<input type='hidden' name='wlAction' value='addToWatchlist'>";
        echo "<input type='submit' value='add to watchlist' class='add'/>";
      }
      echo "</form>";
?>
<script>
<!--
document.observe("dom:loaded",function(){
  $("watchlistFrm").observe("submit",function(e){
    e.stop();
    var opt = Form.serializeElements( $("watchlistFrm").select("input"), {hash:true} );
    opt["partial"] = true;
    new Ajax.Request( $("watchlistFrm").action, {
      method: "post",
      parameters: opt,
      onSuccess: function(transport) {
        if (transport.responseText.length)
        {
          fireSuccessOverlay( opt["wlAction"] == "addToWatchlist" ? "added to watchlist !" : "removed from watchlist !" );
          $("watchlistFrm").update( transport.responseText );
        }
        else
        {
          fireErrorOverlay();
        }
      }
    });
  });
});
//-->
</script>
<?php
      echo "</li>\n";
    }

    echo "</ul>";
    printf("<div id='alltimerank'>alltime top: %s</div>",$this->prod->rank ? "#".(int)$this->prod->rank : "n/a");
  }
  function RenderThumbs()
  {
    echo "<ul class='prodthumbs'>\n";
    echo "<li class='rulez'>".$this->prod->voteup."</li>\n";
    echo "<li class='isok'>".$this->prod->votepig."</li>\n";
    echo "<li class='sucks'>".$this->prod->votedown."</li>\n";
    echo "</ul>\n";
  }
  function RenderLinks()
  {
    echo "<ul>\n";
    echo "<li id='mainDownload'>";
    if ($this->linkCheck)
    {
      if ($this->linkCheck->returnCode == 0
      || $this->linkCheck->returnCode >= 400 && $this->linkCheck->returnCode <= 599)
      {
        printf("<span class='brokenLink error' title='%s'>Link broken!</span> ",$this->linkCheck->returnCode == 0 ? "server not found" : "server returned ".$this->linkCheck->returnCode );
      }
      /*
      TODO: not sure about this
      else if (strstr($this->linkCheck->returnContentType,"octet-stream") === false)
      {
        echo " <span class='brokenLink error'>Link broken!</span>";
      }
      */
    }
    echo "[<a id='mainDownloadLink' href='"._html($this->prod->download)."'>download</a>]";
    echo "</li>\n";

    foreach ($this->downloadLinks as $link)
    {
      echo "<li".(@$link->id?" id='".$link->id."'":"").">[<a href='"._html($link->link)."'>"._html($link->type)."</a>]</li>\n";
    }
    echo "<li>[<a href='mirrors.php?which=".$this->id."'>mirrors...</a>]</li>\n";
    echo "</ul>\n";
  }
  function RenderCredits()
  {
    echo "<ul>";
    foreach($this->credits as $v)
    {
//      $user = PouetUser::Spawn($k);
      if (!$v->user) continue;
      echo "<li>";
      echo $v->user->PrintLinkedAvatar()." ";
      echo $v->user->PrintLinkedName();
      echo " ["._html($v->role)."]";
      echo "</li>";
    }
    echo "</ul>";
  }
  function Render()
  {
    global $currentUser;

    $timer[$this->uniqueID." render"]["start"] = microtime_float();

    echo "<table id='pouetbox_prodmain'>\n";
    echo "<tr id='prodheader'>\n";
    echo "<th colspan='3'>\n";
    echo " <span id='title'><span id='prod-title'>"._html($this->prod->name)."</span>";
    if ($this->prod->groups)
      echo " by ".$this->prod->RenderGroupsLong();
    echo "</span>\n";
    printf("<div id='nfo'>");
    if ($currentUser && $currentUser->CanEditItems())
    {
      printf("[<a href='admin_prod_edit.php?which=%d' class='adminlink'>admin</a>]\n",$this->id);
    }
    if ($currentUser && $currentUser->CanSubmitItems())
    {
      printf("[<a href='submit_modification_request.php?prod=%d'>edit</a>]\n",$this->prod->id);
    }
    if (file_exists(get_local_nfo_path($this->id)))
    {
      $isAmiga = false;
      foreach($this->prod->platforms as $v)
      {
        if (stristr($v["name"],"amiga")!==false)
          $isAmiga = true;
      }
      if ($isAmiga)
        printf("[<a href='prod_nfo.php?which=%d&amp;font=4'>nfo</a>]\n",$this->id);
      else
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

    if ($this->credits)
    {
      echo "<tr>\n";
      echo " <td id='credits' colspan='3' class='r2'>";
      $this->RenderCredits();
      echo "</td>\n";
      echo "</tr>\n";
    }
    else if ($currentUser)
    {
      echo "<tr>\n";
      echo " <td id='credits' colspan='3' class='r2'>";
      echo " <p>this prod has no credits assigned yet! <a href='submit_modification_request.php?prod=".$this->id."&amp;requestType=prod_add_credit'>click here</a> to add some !</p>";
      echo "</td>\n";
      echo "</tr>\n";
    }


    if($this->prod->addedUser)
    {
      echo "<tr>\n";
      echo " <td class='foot' colspan='3'>added on the ".$this->prod->addedDate." by ".$this->prod->addedUser->PrintLinkedName()." ".$this->prod->addedUser->PrintLinkedAvatar()."</td>\n";
      echo "</tr>\n";
    }

    echo "</table>\n";
    $timer[$this->uniqueID." render"]["end"] = microtime_float();
  }

};

class PouetBoxProdPopularityHelper extends PouetBox
{
  var $data;
  var $prod;
  var $id;
  function __construct($prod)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodpopularityhelper";
    $this->title = "popularity helper";
    $this->prod = $prod;
  }

  function RenderContent()
  {
    $url = POUET_ROOT_URL . "prod.php?which=".$this->prod->id;
    echo "<p>increase the popularity of this prod by spreading this URL:</p>\n";
    echo "<input type='text' value='"._html($url)."' size='50' readonly='readonly' />\n";
    echo "<p>or via:\n";

    echo "  <a href='https://www.facebook.com/sharer/sharer.php?u="._html(rawurlencode($url))."'>facebook</a>\n";

    $text = "You should watch \"".$this->prod->name."\" on @pouetdotnet: ".$url;
    echo "  <a href='https://twitter.com/intent/tweet?text="._html(rawurlencode($text))."'>twitter</a>\n";

    echo "  <a href='http://pinterest.com/pin/create/button/?url="._html(rawurlencode($url))."'>pinterest</a>\n";

    echo "  <a href='http://tumblr.com/widgets/share/tool?canonicalUrl="._html(rawurlencode($url))."&amp;posttype=link'>tumblr</a>\n";
    echo "</p>\n";
  }
};

class PouetBoxProdComments extends PouetBox
{
  public $id;
  public $topic;
  public $posts;
  public $credits;
  public $paginator;
  public $data;
  function __construct($id,$main)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodcomments";
    $this->title = "comments";
    $this->id = (int)$id;

    $this->credits = array();
    foreach($main->credits as $credit)
    {
      if(!$credit->user) continue;
      $this->credits[] = $credit->user->id;
    }

    $this->paginator = new PouetPaginator();
  }

  function LoadFromDB()
  {
    $s = new BM_Query();
    $s->AddField("comments.id as id");
    $s->AddField("comments.comment as comment");
    $s->AddField("comments.rating as rating");
    $s->AddField("comments.addedDate as addedDate");
    $s->attach(array("comments"=>"who"),array("users as user"=>"id"));
    $s->AddTable("comments");
    $s->AddOrder("comments.addedDate");
    $s->AddWhere("comments.which=".$this->id);
    $perPage = get_setting("prodcomments");
    if ($perPage != -1)
    {
      $sc = new SQLSelect();
      $sc->AddField("count(*) as c");
      $sc->AddWhere("comments.which=".$this->id);
      $sc->AddTable("comments");

      $commentCount = SQLLib::SelectRow($sc->GetQuery())->c;

      $this->paginator->SetData( "prod.php?which=".$this->id, $commentCount, $perPage, $_GET["page"] );
      $this->paginator->SetLimitOnQuery( $s );
      /*
      $this->commentCount =

      $this->numPages = (int)ceil($this->commentCount / $this->perPage);
      if (!isset($_GET["page"]))
        $this->page = $this->numPages;
      else
        $this->page = (int)$_GET["page"];

      $this->page = (int)max( $this->page, 1 );
      $this->page = (int)min( $this->page, $this->numPages );

      if ($this->numPages > 1)
        $s->SetLimit( $this->perPage, (int)(($this->page-1) * $this->perPage) );
      */
    }
    $r = $s->perform();
    $this->data = $r;
  }

  function RenderBody()
  {
    global $main;
    foreach ($this->data as $c)
    {
      $rating = $c->rating>0 ? "rulez" : ($c->rating<0 ? "sucks" : "");

      $p = $c->comment;
      $p = parse_message($p);

      $author = false;
      if (array_search($c->user->id,$this->credits)!==false)
        $author = true;

      echo "<div class='comment cite-".$c->user->id."".($author?" author":"")."' id='c".$c->id."'>\n";

      echo "  <div class='content'>".$p."</div>\n";

      echo "  <div class='foot'>";
      if ($c->rating)
        echo "<span class='vote ".$rating."'>".$rating."</span>";
      if (@$main->userCDCs[$c->user->id])
      {
        echo "<span class='vote cdc'>cdc</span>";
        unset($main->userCDCs[$c->user->id]);
      }

      echo "<span class='tools' data-cid='".$c->id."'></span> added on the <a href='prod.php?post=".$c->id."'>".$c->addedDate."</a> by ";
      echo $c->user->PrintLinkedName()." ".$c->user->PrintLinkedAvatar();

      echo "</div>\n";
      echo "</div>\n\n";

    }
    $this->paginator->RenderNavbar();
  }

  function RenderFooter()
  {
    echo "</div>\n";
  }
};

class PouetBoxProdLists extends PouetBox
{
  var $id;
  var $topic;
  var $posts;
  var $data;
  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodlists";
    $this->title = "lists containing this prod";
    $this->id = (int)$id;
  }

  function LoadFromDB() {
    $s = new BM_Query();
    $s->AddField("lists.id as id");
    $s->AddField("lists.name as name");
    $s->AddTable("list_items");
    $s->AddJoin("","lists","list_items.list=lists.id");
    $s->attach(array("lists"=>"owner"),array("users as user"=>"id"));
    $s->AddWhere("list_items.itemid=".$this->id);
    $s->AddWhere("list_items.type='prod'");
    $s->AddOrder("lists.name");
    $this->data = $s->perform();
  }

  function RenderBody()
  {
    echo "<ul class='boxlist boxlisttable'>\n";
    foreach($this->data as $list)
    {
      echo "<li>\n";
      printf("  <span><a href='lists.php?which=%d'>%s</a></span>\n",$list->id,_html($list->name));
      echo "  <span>".$list->user->PrintLinkedAvatar()." ".$list->user->PrintLinkedName()."</span>\n";
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
};

class PouetBoxProdSubmitChanges extends PouetBox {
  var $data;
  var $prod;
  var $id;
  function __construct($id) {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodsubmitchanges";
    $this->title = "submit changes";
    $this->id = $id;
  }

  function RenderContent() {
    echo "<p>if this prod is a fake, some info is false or the download link is broken,</p>";
    echo "<p>do not post about it in the comments, it will get lost.</p>";
    //echo "instead, <a href='mailto:pouet@neuromatrice.net?subject=about%20prod%20number%20".$this->id."'>email</a> or <a href='topic.php?which=1024'>post</a> about it.";
    //echo "<p>instead, <a href='topic.php?which=1024'>post</a> about it here ! [<a href='gloperator_log.php?which=".$this->id."&amp;what=prod'>previous edits</a>]</p>";
    echo "<p>instead, <a href='submit_modification_request.php?prod=".$this->id."'>click here</a> !</p>";
    echo "<p>[<a href='gloperator_log.php?which=".$this->id."&amp;what=prod'>previous edits</a>]</p>";
  }

};

class PouetBoxProdSneakyCDCs extends PouetBox {
  var $data;
  var $prod;
  var $id;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodsneakycdcs";
    $this->title = "sneaky cdcs";
  }

  function LoadFromDB() {
    $s = new BM_Query();
    $s->AddField("lists.id as id");
    $s->AddField("lists.name as name");
    $s->AddTable("list_items");
    $s->AddJoin("","lists","list_items.list=lists.id");
    $s->attach(array("lists"=>"owner"),array("users as user"=>"id"));
    $s->AddWhere("list_items.itemid=".$this->id);
    $s->AddWhere("list_items.type='prod'");
    $s->AddOrder("lists.name");
    $this->data = $s->perform();
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

function isEventEligible($event, $prod)
{
  $date = date("Y-m-d");
  if (!($event->votingStartDate <= $date && $date <= $event->votingEndDate))
  {
    return false;
  }
  if ($event->eligibleYear)
  {
    if ($event->eligibleYear != (int)substr($prod->releaseDate,0,4))
    {
      return false;
    }
  }
  if ($event->eligibleTypes)
  {
    $types = explode(",",$event->eligibleTypes);
    $found = false;
    foreach($types as $v)
    {
      if (in_array($v,$prod->types))
      {
        $found = true;
        break;
      }
    }
    if (!$found)
    {
      return false;
    }
  }
  return true;
}

class PouetBoxProdAwardSuggestions extends PouetBox {
  var $data;
  var $prod;
  var $id;
  function __construct($id)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_prodawardsuggestions";
    $this->title = "recommend this prod for an award !";
    $this->prodID = $id;
  }

  function LoadFromDB()
  {
    global $currentUser;
    $s = new BM_Query();
    $s->AddTable("awardssuggestions_votes");
    $s->AddWhere(sprintf_esc("awardssuggestions_votes.prodID='%d'",$this->prodID));
    $s->AddWhere(sprintf_esc("awardssuggestions_votes.userID='%d'",$currentUser->id));
    $_votes = $s->perform();
    $this->votes = array();
    foreach($_votes as $vote) $this->votes[] = $vote->categoryID;
  }

  use PouetForm;
  function Validate($post)
  {
    global $currentUser;

    if (!$currentUser)
      return array("you have to be logged in!");

    return array();
  }

  function Commit($post)
  {
    global $currentUser;
    global $main;
    global $AWARDSSUGGESTIONS_EVENTS;
    global $AWARDSSUGGESTIONS_CATEGORIES;

    SQLLib::Query(sprintf_esc("delete from awardssuggestions_votes where prodID = %d and userID = %d",$this->prodID,$currentUser->id));

    foreach($post["cat"] as $catID)
    {
      $category = $AWARDSSUGGESTIONS_CATEGORIES[$catID];
      $event = $AWARDSSUGGESTIONS_EVENTS[$category->eventID];

      if (isEventEligible($event,$main->prod))
      {
        $a = array(
          "prodID" => $this->prodID,
          "userID" => $currentUser->id,
          "categoryID" => $catID,
        );
        SQLLib::InsertRow("awardssuggestions_votes",$a);
      }
    }

    return array();
  }

  function RenderContent()
  {
    global $main;
    global $AWARDSSUGGESTIONS_EVENTS;
    global $AWARDSSUGGESTIONS_CATEGORIES;

    echo "<p>it's awards season soon, time to remind the juries about what prods you consider outstanding ! recommend this prod to the juries of the following awards :</p>\n";
    echo "<select name='cat[]' multiple='multiple'>";
    foreach($AWARDSSUGGESTIONS_CATEGORIES as $category)
    {
      $event = $AWARDSSUGGESTIONS_EVENTS[$category->eventID];
      if (isEventEligible($event,$main->prod))
      {
        printf("<option value='%d'%s>%s - %s</option>\n",$category->id,in_array($category->id,$this->votes)?" selected='selected'":"",_html($event->name),_html($category->name));
      }
    }
    echo "</select>\n";
    echo "<p>(use ctrl+click to select or deselect more than one category ! you can see all your votes on your <a href='account.php#pouetbox_accountawardsug'>accounts page</a> !)</p>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'>\n";
    echo "   <input type='submit' value='Submit' id='submit'>";
    echo "  </div>\n";
    echo "</div>\n";
  }

};

class PouetBoxProdPost extends PouetBox
{
  public $prod;
  public $prodID;
  public $myVote;
  function __construct($prod)
  {
    global $currentUser;

    parent::__construct();
    $this->prodID = (int)$prod;
    $this->uniqueID = "pouetbox_prodpost";
    $this->title = "add a comment";

    $this->myVote = $currentUser ? SQLLib::SelectRow(sprintf_esc("SELECT * FROM comments WHERE who=%d AND which=%d AND rating!=0 LIMIT 1",(int)$currentUser->id,$this->prodID)) : 0;
  }
  use PouetForm;
  function Validate($post)
  {
    global $currentUser;

    if (!$currentUser)
      return array("you have to be logged in!");

    if (!$currentUser->CanPostInProdComments())
      return array("not allowed lol.");

    if (!is_string_meaningful($post["comment"]))
      return array("not too meaningful, is it...");

    $r = SQLLib::SelectRow(sprintf_esc("SELECT id FROM prods where id=%d",$this->prodID));
    if (!$r)
      return array("you sneaky bastard you >_<");

    $r = SQLLib::SelectRow(sprintf_esc("SELECT comment,who,which FROM comments WHERE which = %d ORDER BY addedDate DESC LIMIT 1",$this->prodID));

    if ($r && $r->who == get_login_id() && $r->comment == $post["comment"])
      return array("ERROR! DOUBLEPOST == ROB IS JARIG!");

    return array();
  }

  function Commit($post)
  {
    $message = trim($post["comment"]);
    $rating = $post["rating"];

    if ($this->myVote)
      $rating = "isok"; // user already has a vote

    $vote = 0;
    switch($rating) {
      case "rulez": $vote = 1; break;
      case "sucks": $vote = -1; break;
      default: $vote = 0; break;
    }

    $a = array();
    $a["addedDate"] = date("Y-m-d H:i:s");
    $a["who"] = get_login_id();
    $a["which"] = $this->prodID;
    $a["comment"] = $message;
    $a["rating"] = $vote;
    SQLLib::InsertRow("comments",$a);

    PouetProd::RecalculateVoteCacheByID($this->prodID);

    @unlink("cache/pouetbox_latestcomments.cache");
    @unlink("cache/pouetbox_topmonth.cache");
    @unlink("cache/pouetbox_stats.cache");

    return array();
  }

  function RenderBody()
  {
    global $currentUser;

    if (!$currentUser)
    {
      $box = new PouetBoxLogin();
      $box->RenderBody();
    }
    else
    {
      if (!$currentUser->CanPostInProdComments())
        return;

      $csrf = new CSRFProtect();
      $csrf->PrintToken();

      echo "<div class='content'>\n";
      echo " <input type='hidden' name='which' value='".(int)$this->prod."'>\n";
      echo " <input type='hidden' name='type' value='comment'>\n";
      if (!$this->myVote)
      {
        echo " <div id='prodvote'>\n";
        echo " this prod\n";
        echo " <label id='ratingrulez'><input type='radio' name='rating' value='rulez'/> rulez</label>\n";
        echo " <label id='ratingpig'><input type='radio' name='rating' value='isok' checked='true'/> is ok</label>\n";
        echo " <label id='ratingsucks'><input type='radio' name='rating' value='sucks'/> sucks</label>\n";
        echo " </div>\n";
      }
      echo " <textarea name='comment' id='comment'></textarea>\n";
      echo " <div><a href='faq.php#BB Code'><b>BB Code</b></a> is allowed here</div>\n";
      echo "</div>\n";
      echo "<div class='foot'>\n";
      echo " <input type='submit' value='Submit' id='prod-post-submit'>";
      echo "</div>\n";
?>
<script>
<!--
document.observe("dom:loaded",function(){
  $$(".tools").each(function(item){
    var cid = item.readAttribute("data-cid");
    item.update("<a href='#'>quote</a> |");
    item.down("a").observe("click",function(e){
      e.stop();
      new Ajax.Request("ajax_prodcomment.php",{
        "method":"post",
        "parameters":$H({"id":cid}).toQueryString(),
        "onSuccess":function(transport){
          $("comment").value += "[quote]" + transport.responseJSON.comment.strip() + "[/quote]";
          try { $("comment").scrollTo(); } catch(ex) {} // needs try-catch because of some dumbass popup blockers
        }
      });
    });
  });
  AddPreviewButton($('prod-post-submit'));
  PreparePostForm( $("pouetbox_prodpost").up("form") );
});
//-->
</script>
<?php
    }
  }

};

$prodid = (int)$_GET["which"];
if (!$prodid)
  $prodid = rand(1,20000);

$form = new PouetFormProcessor();
$form->SetSuccessURL( "prod.php?which=".(int)$prodid, true );

$main = new PouetBoxProdMain($prodid);
$main->Load();

$post = new PouetBoxProdPost($prodid);

$awardSugBox = NULL;
if ($main->prod)
{
  if ($currentUser)
  {
    foreach($AWARDSSUGGESTIONS_EVENTS as $event)
    {
      if (isEventEligible($event,$main->prod))
      {
        $awardSugBox = new PouetBoxProdAwardSuggestions($prodid);
        $awardSugBox->Load();
        $form->Add( "prodawardsuggest", $awardSugBox );
        break;
      }
    }
  }
  $form->Add( "prodpost", $post );

  // OpenGraph docs: https://ogp.me/
  // Twitter card docs: https://developer.twitter.com/en/docs/twitter-for-websites/cards/guides/getting-started
  $metaValues["og:title"] =
  $metaValues["twitter:title"] =
  $TITLE = $main->prod->name.($main->prod->groups ? " by ".$main->prod->RenderGroupsPlain() : "");

  $metaValues["og:type"] = "website";
  $metaValues["twitter:card"] = "summary_large_image";
  $metaValues["twitter:site"] = "@pouetdotnet";

  $desc = implode(" / ",$main->prod->types);
  $desc .= " for ". implode(" / ",array_map(function($i){ return $i["name"]; },$main->prod->platforms));
  if ($main->prod->placings)
  {
    $desc .= ", " . strip_tags($main->prod->placings[0]->PrintResult());
  }
  else
  {
    $desc .= ", released in " . $main->prod->RenderReleaseDate();
  }
  $metaValues["og:description"] =
  $metaValues["twitter:description"] = $desc;

  $linkedData["@type"] = "CreativeWork"; // https://schema.org/CreativeWork
  $linkedData["name"] = $main->prod->name;
  $linkedData["author"] = $main->prod->RenderGroupsPlain();

  $ratingCount = $main->prod->voteup + $main->prod->votedown + $main->prod->votepig;
  if ($ratingCount)
  {
    $linkedData["aggregateRating"] = array(
      "@type" => "AggregateRating",
      "ratingValue" => $main->prod->voteavg,
      "ratingCount" => $ratingCount,
      "bestRating" => 1,
      "worstRating" => -1
    );
  }

  if ($main->screenshotPath)
  {
    $metaValues["og:image"] =
    $metaValues["twitter:image"] =
    $linkedData["image"] = POUET_CONTENT_URL . $main->screenshotPath;
  }


  $form->Process();
}

// AJAX
$csrf = new CSRFProtect();
if (@$_POST["wlAction"] && $currentUser)
{
  if (!$csrf->ValidateToken())
    exit();

  if ($_POST["wlAction"]=="removeFromWatchlist")
  {
    SQLLib::Query(sprintf_esc("delete from watchlist where prodID = %d and userID = %d",$prodid,$currentUser->id));
  }
  else if ($_POST["wlAction"]=="addToWatchlist")
  {
    $a = array("prodID"=>$prodid,"userID"=>$currentUser->id);
    SQLLib::InsertRow("watchlist",$a);
  }
  if ($_POST["partial"])
  {
    $csrf->PrintToken();
    if ($_POST["wlAction"]=="addToWatchlist")
    {
      echo "<input type='hidden' name='wlAction' value='removeFromWatchlist'>";
      echo "<input type='submit' value='remove from watchlist' class='remove'/>";
    }
    else if ($_POST["wlAction"]=="removeFromWatchlist")
    {
      echo "<input type='hidden' name='wlAction' value='addToWatchlist'>";
      echo "<input type='submit' value='add to watchlist' class='add'/>";
    }
    exit();
  }
}

$RSS["export/lastprodcomments.rss.php?prod=".(int)$main->prod->id] = "latest comments on ".$main->prod->name;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
echo "  <div id='prodpagecontainer'>\n";

if ($main->prod)
{
  $main->Render();

  $p = new PouetBoxProdPopularityHelper($main->prod);
  $p->Render();

  if (get_setting("prodcomments")!=0)
  {
    $p = new PouetBoxProdComments($prodid,$main);
    $p->Load();
    if($p->data)
      $p->Render();
  }

  if ($main->userCDCs)
  {
    $p = new PouetBoxProdSneakyCDCs($prodid);
    $p->Render();
  }

  $p = new PouetBoxProdLists($prodid);
  $p->Load();
  if ($p->data)
  {
    $p->Render();
  }

  $p = new PouetBoxProdSubmitChanges($prodid);
  $p->Render();

  if($form)
  {
    $form->Display();
  }

?>
<script>
<!--
document.observe("dom:loaded",function(){
  if (Pouet.isMobile)
  {
    var data = $("screenshot").innerHTML;
    $("screenshot").remove();

    var td = new Element("td",{"colspan":2,"id":"screenshot"}); td.update(data);
    var tr = new Element("tr"); tr.insert(td);

    $("prodheader").parentNode.insertBefore( tr, $("prodheader").nextSibling);
  }
});
//-->
</script>
<?php
}
else
{
  echo "something something prod not found in portuguese something";
}
echo "  </div>\n";
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
