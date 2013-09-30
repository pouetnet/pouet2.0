<?
require_once("include_generic/sqllib.inc.php");
require_once("include_pouet/pouet-box.php");
require_once("include_pouet/pouet-prod.php");
require_once("include_pouet/pouet-user.php");

class PouetBoxProdPost extends PouetBox {
  var $prod;
  function PouetBoxProdPost($prod) {
    parent::__construct();
    $this->prod = (int)$prod;
    $this->uniqueID = "pouetbox_prodpost";
    $this->title = "add a comment";

    $this->myVote = SQLLib::SelectRow(sprintf_esc("SELECT * FROM comments WHERE who=%d AND which=%d AND rating!=0 LIMIT 1",(int)$_SESSION["user"]->id,$this->prod));
  }

  function Validate($post)
  {
    global $currentUser;

    if (!$currentUser)
      return array("you have to be logged in!");

    if (!$currentUser->CanPostInProdComments())
      return array("not allowed lol.");

    $message = trim($post["comment"]);

    if (!$message)
      return array("not too meaningful, is it...");

    $r = SQLLib::SelectRow(sprintf_esc("SELECT id FROM prods where id=%d",$this->prod));
    if (!$r)
      return array("you sneaky bastard you >_<");

    $r = SQLLib::SelectRow(sprintf_esc("SELECT comment,who,which FROM comments WHERE which = %d ORDER BY quand DESC LIMIT 1",$this->prod));

    if ($r && $r->who == get_login_id() && $r->comment == $message)
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
  	$a["quand"] = date("Y-m-d H:i:s");
  	$a["who"] = get_login_id();
  	$a["which"] = $this->prod;
  	$a["comment"] = $message;
  	$a["rating"] = $vote;
    SQLLib::InsertRow("comments",$a);

		$rulez=0;
		$piggie=0;
		$sucks=0;
		$total=0;
		$checktable = array();

    $r = SQLLib::SelectRows("SELECT rating,who FROM comments WHERE which=".$this->prod);
    foreach ($r as $t)
			if(!array_key_exists($t->who, $checktable) || $t->rating != 0)
			  $checktable[$t->who] = $t->rating;

		foreach($checktable as $k=>$v)
    {
      if($v==1) $rulez++;
      else if($v==-1) $sucks++;
      else $piggie++;
      $total++;
    }

		if ($total!=0)
		  $avg = sprintf("%.2f",(float)($rulez*1+$sucks*-1)/(float)$total);
	  else
	    $avg = "0.00";

  	$a = array();
  	$a["voteup"] = $rulez;
  	$a["votepig"] = $piggie;
  	$a["votedown"] = $sucks;
  	$a["voteavg"] = $avg;
    SQLLib::UpdateRow("prods",$a,"id=".$this->prod);

    @unlink("cache/pouetbox_latestcomments.cache");
    @unlink("cache/pouetbox_topmonth.cache");
    @unlink("cache/pouetbox_stats.cache");

    return array();
  }

  function RenderBody() {
    global $currentUser;

    if (!$_SESSION["user"]) {
      require_once("box-login.php");
      $box = new PouetBoxLogin();
      $box->RenderBody();
    } else {
      if (!$currentUser->CanPostInProdComments())
        return;
      echo "<form action='add.php' method='post'>\n";

      $csrf = new CSRFProtect();
      $csrf->PrintToken();

      echo "<div class='content'>\n";
      echo " <input type='hidden' name='which' value='".(int)$this->prod."'>\n";
      echo " <input type='hidden' name='type' value='comment'>\n";
      if (!$this->myVote)
      {
        echo " <div id='prodvote'>\n";
        echo " this prod\n";
        echo " <input type='radio' name='rating' id='ratingrulez' value='rulez'/> <label for='ratingrulez'>rulez</label>\n";
        echo " <input type='radio' name='rating' id='ratingpig' value='isok' checked='true'/> <label for='ratingpig'>is ok</label>\n";
        echo " <input type='radio' name='rating' id='ratingsucks' value='sucks'/> <label for='ratingsucks'>sucks</label>\n";
        echo " </div>\n";
      }
      echo " <textarea name='comment'></textarea>\n";
      echo " <div><a href='faq.php#BB Code'><b>BB Code</b></a> is allowed here</div>\n";
      echo "</div>\n";
      echo "<div class='foot'>\n";
      echo " <input type='submit' value='Submit' id='submit'>";
      echo "</div>\n";
      echo "</form>\n";
?>
<script language="JavaScript" type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  AddPreviewButton($('submit'));
  PreparePostForm( $$("#pouetbox_prodpost form").first() );
});
//-->
</script>
<?
    }
  }

};

?>
