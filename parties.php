<?php
require_once("bootstrap.inc.php");

class PouetBoxPartyList extends PouetBox
{
  public $letter;
  public $letterselect;
  public $parties;
  public $partyyears;
  public $partylinks;
  function __construct($letter)
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_partylist";

    $letter = substr($letter,0,1);
    if (preg_match("/^[a-z]$/",$letter))
      $this->letter = $letter;
    else
      $this->letter = "#";

    $a = array();
    $a[] = "<a href='parties.php?pattern=%23'>#</a>";
    for($x=ord("a");$x<=ord("z");$x++)
      $a[] = sprintf("<a href='parties.php?pattern=%s'>%s</a>",chr($x),chr($x));

    $this->letterselect = "[ ".implode(" |\n",$a)." ]";
  }

  function RenderHeader()
  {
    echo "\n\n";
    echo "<div class='pouettbl' id='".$this->uniqueID."'>\n";
    echo " <div class='letterselect'>".$this->letterselect."</div>\n";
  }

  function RenderFooter()
  {
    echo " <div class='letterselect'>".$this->letterselect."</div>\n";
    echo "</div>\n";
  }

  function Load()
  {
    $s = new BM_query("parties");
    if ($this->letter=="#")
      $s->AddWhere(sprintf("name regexp '^[^a-z]'"));
    else
      $s->AddWhere(sprintf("name like '%s%%'",$this->letter));
    $s->AddOrder("name");
    $this->parties = $s->perform();

    if ($this->parties)
    {
      $ids = array();
      foreach($this->parties as $group) $ids[] = $group->id;
      $idstr = implode(",",$ids);

      $rows = SQLLib::selectRows(sprintf("SELECT count(*) as c, party, party_year FROM `prods` WHERE party in (%s) GROUP by party, party_year order by party_year",$idstr));
      $this->partyyears = array();
      foreach($rows as $row)
        if ($row->party)
          $this->partyyears[$row->party][$row->party_year] = $row->c;

      $rows = SQLLib::selectRows(sprintf("SELECT * FROM `partylinks` WHERE party in (%s)",$idstr));
      $this->partylinks = array();
      foreach($rows as $row)
        if ($row->party)
          $this->partylinks[$row->party][$row->year] = $row;
    }
  }

  function RenderBody()
  {
    echo "<table class='boxtable'>\n";
    echo "<tr>\n";
    echo "  <th>partyname</th>\n";
    echo "  <th>year</th>\n";
    echo "  <th>releases</th>\n";
    echo "  <th>download</th>\n";
    echo "</tr>\n";
    foreach ($this->parties as $party)
    {
      $p = 0;
      if (!@$this->partyyears[$party->id])
        $this->partyyears[$party->id][""] = 0;
      foreach($this->partyyears[$party->id] as $year=>$count)
      {
        echo "<tr>\n";
        if ($p==0)
          echo "  <td class='partyname'>".$party->RenderFull()."</td>\n";
        else
          echo "  <td></td>\n";
        echo "  <td>\n";
        if ($year)
          echo "<a href='party.php?which=".$party->id."&amp;when=".$year."'>".$year."</a> ";
        if (@$this->partylinks[$party->id][$year])
        {
          if($this->partylinks[$party->id][$year]->slengpung)
            echo " [<a href='http://www.slengpung.com/?eventid=".(int)$this->partylinks[$party->id][$year]->slengpung."'>slengpung</a>]";
          if($this->partylinks[$party->id][$year]->csdb)
            echo " [<a href='http://csdb.dk/event/?id=".(int)$this->partylinks[$party->id][$year]->csdb."'>csdb</a>]";
          if($this->partylinks[$party->id][$year]->zxdemo)
            echo " [<a href='http://zxdemo.org/party.php?id=".(int)$this->partylinks[$party->id][$year]->zxdemo."'>zxdemo</a>]";
          if($this->partylinks[$party->id][$year]->artcity)
            echo " [<a href='http://artcity.bitfellas.org/index.php?a=search&type=tag&text=".rawurlencode($this->partylinks[$party->id][$year]->artcity)."'>artcity</a>]";
        }
        echo "</td>\n";
        echo "  <td>".$count."</td>\n";
        echo "  <td>";

        if(@$this->partylinks[$party->id][$year]->download)
          echo "[<a href='".$this->partylinks[$party->id][$year]->download."'>prods</a>] ";

        if(file_exists($party->GetResultsLocalFileName($year)))
          echo $party->RenderResultsLink( $year );

        echo "</td>\n";
        echo "</tr>\n";
        $p++;
      }
    }
    echo "</table>\n";
  }
};
///////////////////////////////////////////////////////////////////////////////

$pattern = @$_GET["pattern"] ? @$_GET["pattern"] : chr(rand(ord("a"),ord("z")));
$p = new PouetBoxPartyList($pattern);
$p->Load();
$TITLE = "parties: ".$p->letter;

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";
if($p) $p->Render();
echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");
?>
