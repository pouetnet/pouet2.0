<?
include_once("include_generic/sqllib.inc.php");
include_once("include_pouet/pouet-box.php");
include_once("include_pouet/pouet-prod.php");
include_once("include_pouet/pouet-user.php");

class PouetBoxLatestOneliner extends PouetBoxCachable {
  var $data;
  function PouetBoxLatestOneliner() {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestoneliner";
    $this->title = "the so famous pouët.net oneliner";

    $this->limit = 5;
  }

  function Validate($post) 
  {
    $message = trim($post["message"]);
    
    if (!$message)
      return array("not too meaningful, is it...");
    
    $r = SQLLib::SelectRow("SELECT who FROM oneliner ORDER BY quand DESC LIMIT 1");
    
    if ($r->who == $_SESSION["user"]->id)
      return array("ERROR! DOUBLEPOST == ROB IS JARIG!");
  }
  
  function Commit($post) 
  {
    $message = trim($post["message"]);

  	$a = array();
  	$a["who"] = $_SESSION["user"]->id;
  	$a["quand"] = date("Y-m-d H:i:s");
  	$a["message"] = $message;
  
    SQLLib::InsertRow("oneliner",$a);  
    
    $this->ForceCacheUpdate();
    
    return array();
  }
  function LoadFromCachedData($data) {
    $this->data = unserialize($data);
  }
  function GetCacheableData() {
    return serialize($this->data);
  }
  function SetParameters($data)
  {
    if (isset($data["limit"])) $this->limit = $data["limit"];
  }
  
  function LoadFromDB() {
    $s = new BM_query();
    $s->AddField("message");
    $s->AddTable("oneliner");
    $s->attach(array("oneliner"=>"who"),array("users as user"=>"id"));
    $s->AddOrder("oneliner.quand desc");
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();
    $this->data = array_reverse($this->data);
  }

  function RenderBody() {
    echo "<ul class='boxlist'>\n";
    $data = array_slice($this->data,-1 * $this->limit,NULL,true);
    foreach ($data as $r) {
      if (!$r->user) continue;
      echo "<li>\n";
      echo $r->user->PrintLinkedAvatar()."\n";

      $p = $r->message;
      $p = _html($p);
      //$p = bbencode($p,true);
      //$p = nl2br($p);
      $p = preg_replace("/([a-z]+:\/\/\S+)/","<a href='$1'>link me beautiful</a>",$p);
      $p = better_wordwrap($p,80," ");

      echo $p;
      echo "</li>\n";
//      if ($n == get_setting("indexoneliner")) break;
    }
    echo "</ul>\n";
    ?>
    <script type="text/javascript">
    document.observe("dom:loaded",function(){ Youtubify($("pouetbox_latestoneliner")); });
    </script>
    <?
  }
  function RenderFooter() {
    if (!$_SESSION["user"]) {
      echo "  <div class='foot'><a href='oneliner.php'>more</a>...</div>\n";
    } else {
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
      $funnytext = "pouët 2.0: ünicøde иow шőrks in the σneliήer";
      
      
      echo "  <div class='foot loggedin'>\n";
      echo "   <span><a href='oneliner.php'>more</a>...</span>\n";      
      echo "   <form action='add.php' method='post'>\n";

      $csrf = new CSRFProtect();
      $csrf->PrintToken();

      echo "    <input type='hidden' name='type' value='oneliner'>\n";

      // we dont use placeholder="" because we want people to be able to post the default nonsense
      echo "    <input type='text' name='message' value='"._html($funnytext)."' size='50' id='onelinermsg'/>\n";
      echo "    <input type='submit' value='Submit'/>\n";
      echo "   </form>\n";
      echo "  </div>\n";
?>
<script type="text/javascript">
var onelinerClicked = false;
$("onelinermsg").observe("focus",function(){
  if (!onelinerClicked)
    $("onelinermsg").value = "";
  onelinerClicked = true;
});
</script>
<?
    }
    echo "</div>\n";
  }
};

?>