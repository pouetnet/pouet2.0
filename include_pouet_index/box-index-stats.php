<?php
class PouetBoxIndexStats extends PouetBoxCachable
{
  var $data;
  var $fields;
  var $links;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_stats";
    $this->title = "some stats";
    $this->fields = array("prods", "groups", "parties", "boards", "users", "comments");
    $this->links = array("prodlist.php", "groups.php", "parties.php", "boards.php", "userlist.php", "comments.php");
  }

  function LoadFromCachedData($data)
  {
    $this->data = unserialize($data);
  }

  function GetCacheableData()
  {
    return serialize($this->data);
  }

  function LoadFromDB()
  {
    $a = array("prods", "groups", "parties", "boards", "users", "comments");
    foreach($this->fields as $v) {
      $field = $v == "users" ? "registerDate" : "addedDate";
      $this->data[$v."_all"] = (int)SQLLib::SelectRow("SELECT count(0) as c FROM ".$v)->c;
      $this->data[$v."_24h"] = (int)SQLLib::SelectRow("SELECT count(0) as c FROM ".$v." WHERE (UNIX_TIMESTAMP()-UNIX_TIMESTAMP(".$field."))<=3600*24")->c;
    }
    $this->data["usersSeen24h"] = (int)SQLLib::SelectRow("SELECT count(0) as c FROM users WHERE (UNIX_TIMESTAMP()-UNIX_TIMESTAMP(lastLogin))<=3600*24")->c;
    $this->data["youtube"] = (int)SQLLib::SelectRow("SELECT COUNT(0) AS c FROM prods LEFT JOIN downloadlinks ON downloadlinks.prod = prods.id AND downloadlinks.type LIKE '%youtube%' WHERE downloadlinks.type IS NOT NULL")->c;
  }

  function Render()
  {
    echo "<table class='boxtable' id='".$this->uniqueID."'>\n";
    $n = 0;
    echo "<tr>\n";
    echo "  <th class='header'>".$this->title."</th>\n";
    echo "  <th class='right'>-24h</th>\n";
    echo "</tr>\n";
    foreach($this->fields as $k=>$v) {
      echo "<tr>\n";
      echo "  <td class='r".(($n++&1)+1)."'>".$this->data[$v."_all"]." <a href='".$this->links[$k]."'>".$v."</a></td>\n";
      echo "  <td class='r".(($n++&1)+1)." stat'>+ ".$this->data[$v."_24h"]."</td>\n";
      echo "</tr>\n";
    }
    echo "<tr>\n";
    echo "  <td class='r".(($n++&1)+1)."'>".$this->data["usersSeen24h"]." users seen in the last 24h</td>\n";
    echo "  <td class='r".(($n++&1)+1)." stat'>&nbsp;</td>\n";
    echo "</tr>\n";
    echo "<tr>\n";
    echo "  <td class='r".(($n++&1)+1)."'>progress to the youtube singularity: ".sprintf("%.2f",$this->data["youtube"] / $this->data["prods_all"] * 100.0)."%</td>\n";
    echo "  <td class='r".(($n++&1)+1)." stat'>&nbsp;</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
  }

};

$indexAvailableBoxes[] = "Stats";
?>