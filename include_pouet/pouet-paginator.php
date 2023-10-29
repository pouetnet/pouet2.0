<?php
class PouetPaginator
{
  public $url;
  public $itemCount;
  public $perPage;
  public $numPages;
  public $page;
  public $query;
  function SetData( $url, $total, $perPage, $curPage, $defaultToLast = true )
  {
    $this->url = parse_url($url);
    parse_str( @$this->url["query"] ?: "", $this->query );

    $this->itemCount = $total;
    $this->perPage = $perPage;

    $this->numPages = (int)ceil($this->itemCount / $this->perPage);
    if (!$curPage)
      $this->page = ($defaultToLast ? $this->numPages : 1);
    else
      $this->page = (int)$curPage;

    $this->page = (int)max( $this->page, 1 );
    $this->page = (int)min( $this->page, $this->numPages );
  }
  function SetLimitOnQuery( &$query )
  {
    if ($this->numPages > 1)
      $query->SetLimit( $this->perPage, (int)(($this->page-1) * $this->perPage) );
  }
  function RenderNavbar() {
    if ($this->numPages <= 1)
      return;
    echo "<div class='navbar'>\n";
    if ($this->page > 1)
      echo "  <div class='prevpage'><a href='".$this->url["path"]."?"._html(http_build_query( array_merge($this->query,array("page"=>$this->page - 1)) ))."'>previous page</a></div>\n";
    if ($this->page * $this->perPage < $this->itemCount)
      echo "  <div class='nextpage'><a href='".$this->url["path"]."?"._html(http_build_query( array_merge($this->query,array("page"=>$this->page + 1)) ))."'>next page</a></div>\n";
    echo "  <div class='selector'>";

    echo "  <form action='".$this->url["path"]."' method='get'>\n";
    foreach($this->query as $k=>$v)
      echo "  <input type='hidden' name='"._html($k)."' value='"._html($v)."'/>\n";
    echo "   go to page <select name='page'>\n";

    for ($x = 1; $x <= $this->numPages; $x++)
      echo "      <option value='".$x."'".($x==$this->page?" selected='selected'":"").">".$x."</option>\n";

    echo "   </select> of ".$this->numPages."\n";
    echo "  <input type='submit' value='Submit'/>\n";
    echo "  </form>\n";
    echo "  </div>\n";
    echo "</div>\n";
  }
}
?>
