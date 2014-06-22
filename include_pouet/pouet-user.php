<?
class PouetUser extends BM_Class {
  static function getTable () { return "users"; }
  static function getFields() { return array("id","nickname","level","avatar","glops","quand","lastLogin"); }
  static function getExtendedFields() { return array("im_id","im_type","udlogin","sceneIDLastRefresh","sceneIDData","ojuice","slengpung","csdb","zxdemo","demozoo","lastip","lasthost"); }
  function PrintLinkedAvatar() {
//    return "<a href='user.php?who=".$this->id."' class='usera' title=\""._html($this->nickname)."\"><img src='".POUET_CONTENT_URL."avatars/".rawurlencode($this->avatar)."' alt=\""._html($this->nickname)."\" class='avatar'/></a>";
    return sprintf("<a href='user.php?who=%d' class='usera' title=\"%s\"><img src='".POUET_CONTENT_URL."avatars/%s' alt=\"%s\" class='avatar'/></a>",
      $this->id,_html($this->nickname),rawurlencode($this->avatar),_html($this->nickname));
  }
  function PrintLinkedName() {
    $classes = array("user");
    if ((time() - strtotime($this->lastLogin)) < 5 * 60) $classes[] = "online";
    if ($this->IsBanned()) $classes[] = "banned";
    //return "<a href='user.php?who=".(int)$this->id."' class='".implode(" ",$classes)."'>"._html($this->nickname)."</a>";
    return sprintf("<a href='user.php?who=%d' class='%s'>%s</a>",$this->id,implode(" ",$classes),_html($this->nickname));
  }
  function Create() {
    $a = array();
    $a["id"] = $this->id;
    $a["nickname"] = $this->nickname;
    $a["avatar"] = $this->avatar;
    $a["quand"] = date("Y-m-d H:i:s");
    $a["lastip"] = $_SERVER["REMOTE_ADDR"];
    $a["lasthost"] = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
    SQLLib::InsertRow("users",$a);
  }
  function CalculateGlops()
  {
    $glops = 0;
    $this->stats["prods"]       = SQLLib::SelectRow(sprintf_esc("SELECT count(0) AS c FROM prods WHERE added=%d",$this->id))->c;
    $this->stats["groups"]      = SQLLib::SelectRow(sprintf_esc("SELECT count(0) AS c FROM groups WHERE added=%d",$this->id))->c;
    $this->stats["parties"]     = SQLLib::SelectRow(sprintf_esc("SELECT count(0) AS c FROM parties WHERE added=%d",$this->id))->c;
    $this->stats["screenshots"] = SQLLib::SelectRow(sprintf_esc("SELECT count(0) AS c FROM screenshots WHERE user=%d",$this->id))->c;
    $this->stats["nfos"]        = SQLLib::SelectRow(sprintf_esc("SELECT count(0) AS c FROM nfos WHERE user=%d",$this->id))->c;
    $this->stats["comments"]    = SQLLib::SelectRow(sprintf_esc("SELECT COUNT(DISTINCT which) AS c  FROM comments WHERE who=%d",$this->id))->c;
    $this->stats["logos"]       = SQLLib::SelectRow(sprintf_esc("SELECT COUNT(*) AS c FROM logos WHERE (author1=%d or author2=%d)",$this->id,$this->id) )->c;
    $this->stats["logosVote"]   = SQLLib::SelectRow(sprintf_esc("SELECT COUNT(*) AS c FROM logos WHERE vote_count > 0 and (author1=%d or author2=%d)",$this->id,$this->id) )->c;
    $this->stats["requestGlops"] = SQLLib::SelectRow(sprintf_esc("SELECT COUNT(*) AS c FROM modification_requests WHERE userID=%d AND approved = 1 AND requestType in ('prod_add_credit')",$this->id) )->c;
    $this->stats["topics"]      = SQLLib::SelectRow(sprintf_esc("SELECT count(0) AS c FROM bbs_topics WHERE userfirstpost=%d",$this->id))->c;
    $this->stats["posts"]       = SQLLib::SelectRow(sprintf_esc("SELECT count(0) AS c FROM bbs_posts WHERE author=%d",$this->id))->c;
    if($this->udlogin)
      $this->stats["ud"] = (int)round(SQLLib::SelectRow(sprintf_esc("SELECT points FROM ud WHERE login='%s'",$this->udlogin))->points / 1000);
    else
      $this->stats["ud"] = 0;

    $glops +=  2 * $this->stats["prods"];
    $glops +=  1 * $this->stats["groups"];
    $glops +=  1 * $this->stats["parties"];
    $glops +=  1 * $this->stats["screenshots"];
    $glops +=  1 * $this->stats["nfos"];
    $glops +=  1 * $this->stats["comments"];
    $glops += 20 * $this->stats["logosVote"];
    $glops +=  1 * $this->stats["ud"];
    $glops +=  1 * $this->stats["requestGlops"];
    
    return $glops;
  }
  function UpdateGlops()
  {
    $this->glops = $this->CalculateGlops();
    SQLLib::UpdateRow("users",array("glops"=>$this->glops),sprintf_esc("id=%d",$this->id));
  }
  function GetSceneIDData( $cached = true )
  {
    if ($cached)
    {
      if (time() - strtotime( $this->sceneIDLastRefresh ) < 60 * 60 * 12)
      {
        if ($this->sceneIDData)
          return unserialize( $this->sceneIDData );
      }
    }
    global $sceneID;
    try
    {
      $rv = $sceneID->getUserInfoById( $this->id )->asAssoc();
    }
    catch(SceneIdException $e)
    {
      return NULL;
    }

    if ((int)$rv["returnCode"] == 10)
    {
      //if ($cached)
      {
        SQLLib::UpdateRow("users",array(
          "sceneIDLastRefresh"=>date("Y-m-d H:i:s"),
          "sceneIDData"=>serialize($rv["user"])
        ),sprintf_esc("id=%d",$this->id));
      }
      return $rv["user"];
    }
    else
    {
      return NULL;
    }
  }



  function GetLevel()
  {
    return $this->level;
  }
  function IsGloperator()
  {
    return ($this->level=="gloperator" || $this->level=="moderator" || $this->level=="administrator");
  }
  function IsModerator()
  {
    return ($this->level=="moderator" || $this->level=="administrator");
  }
  function IsAdmin()
  {
    return ($this->level=="administrator");
  }
  function IsAdministrator()
  {
    return $this->IsAdmin();
  }
  function IsBanned()
  {
    return ($this->level=="banned");
  }



  function CanPostInOneliner()
  {
    return true;
  }
  function CanPostInBBS()
  {
    return true;
  }
  function CanPostInProdComments()
  {
    return true;
  }
  function CanSubmitItems()
  {
    $very_smart_people = array(
      3254,
      27338,
      25511,
      4627,
      24880,
      58309,
    );
    return array_search($this->id,$very_smart_people) === false;
  }
  function CanDeleteItems()
  {
    return $this->IsModerator();
  }
  function CanEditItems()
  {
    return $this->IsGloperator();
  }
  function CanEditBBS()
  {
    return $this->IsModerator();
  }
};

BM_AddClass("PouetUser");
?>
