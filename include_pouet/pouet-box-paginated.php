<?php
class PouetBoxPaginated extends PouetBox 
{
  function __construct() {
    parent::__construct();
  }
  
  function GetCurrentPage() { return 1; }
  function CollectAdditionalProdData() { return false; }
  
  function BuildURL( $param ) {
    $query = array_merge($_GET,$param);
    unset( $query["reverse"] );
    if($param["order"] && $_GET["order"] == $param["order"] && !$_GET["reverse"])
      $query["reverse"] = 1;
    return htmlspecialchars("prodlist.php?" . http_build_query($query));
  }
  function LoadFromDB() 
  {
    $perPage = $this->GetPerPage();
    $this->page = (int)max( 1, (int)$this->GetCurrentPage() );
    
    $s = $thiss->GetQueryObject();

    $s->SetLimit((int)(($this->page-1) * $perPage) . "," . $perPage);
    
    //echo $s->GetQuery();
    
    $this->items = $s->performWithCalcRows( &$this->count );
    if ($this->CollectAdditionalProdData())
    {
      PouetCollectPlatforms($this->items);
      PouetCollectAwards($this->items);
    }
    $this->maxviews = SQLLib::SelectRow("SELECT MAX(views) as m FROM prods")->m;
  }

  function Render() {
    echo "<table id='".$this->uniqueID."' class='boxtable'>\n";
    $headers = array(
      "type"=>"type",
      "name"=>"prodname",
      "platform"=>"platform",
      "group"=>"group",
      "party"=>"release party",
      "release"=>"release",
      "added"=>"added",
      "thumbup"=>"<span class='rulez' title='rulez'>rulez</span>",
      "thumbpig"=>"<span class='isok' title='piggie'>piggie</span>",
      "thumbdown"=>"<span class='sucks' title='sucks'>sucks</span>",
      "avg"=>"avg",
      "views"=>"popularity",
    );
    echo "<tr class='sortable'>\n";
    foreach($headers as $key=>$text)
    {
      $out = sprintf("<th><a href='%s' class='%s%s' id='%s'>%s</a></th>\n",
        $this->BuildURL(array("order"=>$key)),$_GET["order"]==$key?"selected":"",($_GET["order"]==$key && $_GET["reverse"])?" reverse":"","sort_".$key,$text); 
      if ($key == "type" || $key == "name") $out = str_replace("</th>","",$out);
      if ($key == "platform" || $key == "name") $out = str_replace("<th>"," ",$out);
      echo $out;
    }
    echo "</tr>\n";

    $n = 0;
    foreach ($this->items as $item) {
      echo "<tr class='bg".(($n++&1)+1)."'>\n";
      echo $this->RenderItem(&$item);
      echo "</tr>\n";
    }
    
    $perPage = $this->GetPerPage();
    
    echo "<tr>\n";
    echo "<td class='nav' colspan=".(count($headers)-2).">\n";
    
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
?>