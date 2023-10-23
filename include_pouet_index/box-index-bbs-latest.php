<?php
class PouetBoxIndexLatestBBS extends PouetBoxCachable
{
  public $data;
  public $limit;
  public $hideResidue;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestbbs";
    $this->title = "the oldskool pouët.net bbs";
    $this->cacheTime = 60;

    $this->limit = 10;
    $this->hideResidue = true;
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
    if (isset($data["hideResidue"])) $this->hideResidue = $data["hideResidue"];
  }
  function GetParameterSettings()
  {
    return array(
      "limit"      => array("name"=>"number of topics visible","default"=>10,"min"=>1,"max"=>POUET_CACHE_MAX),
      "hideResidue"=> array("name"=>"hide residue topics","default"=>true,"type"=>"checkbox"),
    );
  }

  function LoadFromDB()
  {
    $s = new BM_query();
    $s->AddField("bbs_topics.id as id");
    $s->AddField("bbs_topics.topic as topic");
    $s->AddField("bbs_topics.count as count");
    $s->AddField("bbs_topics.category as category");
    $s->AddField("bbs_topics.lastpost as lastpost");
    $s->AddField("bbs_topics.closed as closed");
    $s->AddTable("bbs_topics");
    $s->attach(array("bbs_topics"=>"userfirstpost"),array("users as firstuser"=>"id"));
    $s->attach(array("bbs_topics"=>"userlastpost"),array("users as lastuser"=>"id"));
    $s->AddOrder("lastpost desc");
    $s->SetLimit(POUET_CACHE_MAX);

    $this->data = $s->perform();
  }

  function RenderBody()
  {
    global $THREAD_CATEGORIES;
    echo "<table class='boxtable'>\n";
    $n = 0;
    foreach ($this->data as $r) {
      //if (get_setting("indexbbsnoresidue"))
      if ($this->hideResidue)
      {
        if ($r->category == "residue") continue;
      }
      printf("<tr class='%s'>\n",$r->closed?"closed":"");
      echo "  <td class='avatar'>".$r->firstuser->PrintLinkedAvatar()."</td>\n";
      echo "  <td class='category'>"._html($r->category)."</td>\n";
      echo "  <td class='topic'><a href='topic.php?which=".(int)$r->id."'>"._html($r->topic)."</a></td>\n";
      echo "  <td class='count' title='last post "._html(dateDiffReadable(time(),$r->lastpost))." ago - "._html($r->lastpost)."'>".$r->count."</td>\n";
      echo "  <td class='avatar'>".$r->lastuser->PrintLinkedAvatar()."</td>\n";
      echo "</tr>\n";
      if (++$n == $this->limit) break;
    }
    echo "</table>\n";
  }
  function RenderFooter()
  {
    echo "  <div class='foot'><a href='bbs.php'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

$indexAvailableBoxes[] = "LatestBBS";
?>