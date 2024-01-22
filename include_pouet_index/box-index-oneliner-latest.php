<?php
class PouetBoxIndexLatestOneliner extends PouetBoxCachable
{
  public $data;
  public $limit;
  public $showTimestamps;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestoneliner";
    $this->title = "the so famous pouët.net oneliner";

    $this->limit = 5;
    $this->showTimestamps = false;
  }
  use PouetForm;
  function Validate($post)
  {
    global $currentUser;
    $message = trim($post["message"]);

    if (!is_string_meaningful($message))
      return array("not too meaningful, is it...");

    if (!$currentUser || !$currentUser->CanPostInOneliner())
      return array("just no.");

    $r = SQLLib::SelectRow("SELECT who FROM oneliner ORDER BY addedDate DESC LIMIT 1");

    if ($r->who == $currentUser->id)
      return array("ERROR! DOUBLEPOST == ROB IS JARIG!");
  }

  function Commit($post)
  {
    global $currentUser;
    $message = trim($post["message"]);

  	$a = array();
  	$a["who"] = $currentUser->id;
  	$a["addedDate"] = date("Y-m-d H:i:s");
  	$a["message"] = $message;

    SQLLib::InsertRow("oneliner",$a);

    $this->ForceCacheUpdate();

    return array();
  }
  function LoadFromCachedData($data)
  {
    $this->data = unserialize($data);
  }
  function GetCacheableData()
  {
    return serialize($this->data);
  }

  use PouetFrontPage;
  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
    if (isset($data["showTimestamps"])) $this->showTimestamps = $data["showTimestamps"];
  }
  function GetParameterSettings()
  {
    return array(
      "limit" => array("name"=>"number of oneliners visible","default"=>5,"min"=>1,"max"=>POUET_CACHE_MAX),
      "showTimestamps" => array("name"=>"show timestamps","type"=>"checkbox"),
    );
  }

  function LoadFromDB()
  {
    $s = new BM_query();
    $s->AddField("message");
    $s->AddField("addedDate");
    $s->AddTable("oneliner");
    $s->attach(array("oneliner"=>"who"),array("users as user"=>"id"));
    //$s->AddOrder("oneliner.addedDate desc, oneliner.id desc");
    $s->AddOrder("oneliner.id desc");
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();
    $this->data = array_reverse($this->data);
  }

  function RenderBody()
  {
    echo "<ul class='boxlist'>\n";
    $data = array_slice($this->data,-1 * $this->limit,NULL,true);
    foreach ($data as $r) {
      if (!$r->user) continue;
      echo "<li>\n";
      if ($this->showTimestamps)
        echo "<time datetime='".$r->addedDate."' title='".$r->addedDate."'>".date("H:i",strtotime($r->addedDate))."</time> ";
      echo $r->user->PrintLinkedAvatar()."\n";

      $p = $r->message;
      $p = _html($p);
      //$p = bbencode($p,true);
      //$p = nl2br($p);
      $p = preg_replace("/([a-z]+:\/\/\S+)/","<a href='$1' rel='external'>link me beautiful</a>",$p);
      $p = better_wordwrap($p,40," ");

      echo $p;
      echo "</li>\n";
//      if ($n == get_setting("indexoneliner")) break;
    }
    echo "</ul>\n";
    ?>
    <script>
    document.observe("dom:loaded",function(){ StubLinksToDomainName($("pouetbox_latestoneliner")); });
    </script>
    <?php    
  }
  function RenderFooter()
  {
    global $currentUser;
    if (!$currentUser || !$currentUser->CanPostInOneliner())
    {
      echo "  <div class='foot'><a href='oneliner.php'>more</a>...</div>\n";
    }
    else
    {
      //$funnytext = "have fun";
      //$funnytext = "get a cookie coz u'll need one to post";
      //$funnytext = "demo my ipod me beautiful!";
      //$funnytext = "bbcode and unicode doesnt work on oneliner";
      //$funnytext = "Most people including myself have some sensibility";
      //$funnytext = "### song, people dancing ###";
      //$funnytext = "PANTS OFF!";
      //$funnytext = "The world may now !";
      //$funnytext = "Captain: I'm in Mensa.";
      //$funnytext = "SHOW US YOUR";
      //$funnytext = "remember: NO CAPES!";
      //$funnytext = "NO THURSDAY ARRIVALS!";
      //$funnytext = "if garfield was a criminal, we would purchase him until afghanistan.";
      //$funnytext = "crashes indeed.. but wow! NOOON..";
      //$funnytext = "time is to unicode on the onliner";
      //$funnytext = "pou\303\253t 2.0: \303\274nic\303\270de \320\270ow \321\210\305\221rks in the \317\203neli\316\256er";
      //$funnytext = "that moment when you accidentally mistyped demozoo";
      //$funnytext = "I am actually more of a rebel than ever.";
      //$funnytext = "But seriously.";
      //$funnytext = "It is just a video coming from the demoscene. We use it for the track.";
      //$funnytext = "next meme please";
      //$funnytext = "Okay? Yes!";
      $funnytext = "and this will affect the Demoscene.";

      echo "  <div class='foot loggedin'>\n";
      echo "   <span><a href='oneliner.php'>more</a>...</span>\n";
      echo "   <form id='frmIndexOneliner' action='add.php' method='post'>\n";

      $csrf = new CSRFProtect();
      $csrf->PrintToken();

      echo "    <input type='hidden' name='type' value='oneliner'>\n";

      // we dont use placeholder="" because we want people to be able to post the default nonsense
      echo "    <input type='text' name='message' value='"._html($funnytext)."' id='onelinermsg' maxlength='300'/>\n";
      echo "    <input type='submit' value='Submit'/>\n";
      echo "   </form>\n";
      echo "  </div>\n";
?>
<script>
var onelinerClicked = false;
$("onelinermsg").observe("focus",function(){
  if (!onelinerClicked)
    $("onelinermsg").value = "";
  onelinerClicked = true;
});
$("frmIndexOneliner").observe("submit",function(ev){
  if ($("onelinermsg").value.indexOf("[url")!=-1) {
    alert("BBCode doesn't work in the oneliner!");
    ev.stop();
  }
});
</script>
<?php
    }
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "LatestOneliner";
?>
