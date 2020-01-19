<?
class PouetBoxEditConnectionsBase extends PouetBox
{
  public static $slug = "None";
  function __construct()
  {
    parent::__construct();
    $this->allowDelete = true;
  }  
  function GetRow($id)
  {
    foreach($this->data as $v)
      if ($v->id == $id)
        return $v;
    return new stdClass();
  }
  function RenderEditRow($row)
  {
  }
  function RenderNormalRow($row)
  {
  }
  function RenderNormalRowEnd($row)
  {
    echo "<td>";
    $csrf = new CSRFProtect();
    $csrf->PrintToken();
    printf("    <a href='%s?which=%d&amp;edit%s=%d' class='edit'>edit</a>",$_SERVER["SCRIPT_NAME"],$this->id,static::$slug,$row->id);
    if ($this->allowDelete)
    {
      printf("  | <a href='%s?which=%d&amp;del%s=%d' class='delete'>delete</a>\n",$_SERVER["SCRIPT_NAME"],$this->id,static::$slug,$row->id);
    }
    echo "</td>\n";
  }
  function RenderDeleteRowEnd($row)
  {
    if (!$this->allowDelete)
    {
      return;
    }
    echo "<td>";
    $csrf = new CSRFProtect();
    $csrf->PrintToken();
    echo "<input type='hidden' name='del".static::$slug."' value='".$row->id."'/>";
    echo "<input type='submit' value='Delete!'/>";
    echo "</td>\n";
  }
  function RenderEditRowEnd($row)
  {
    echo "<td>";
    $csrf = new CSRFProtect();
    $csrf->PrintToken();
    if ($row->id)
      echo "<input type='hidden' name='edit".static::$slug."ID' value='".$row->id."'/>";
    echo "<input type='submit' value='Submit'/>";
    echo "</td>\n";
  }
  function RenderBody()
  {
    echo "<table class='boxtable'>\n";
    echo "  <tr>\n";
    foreach($this->headers as $v)
      echo "    <th>"._html($v)."</th>\n";
    echo "    <th>&nbsp;</th>\n";
    echo "  </tr>\n";
    foreach($this->data as $row)
    {
      echo "  <tr>\n";
      if ($_GET["edit" . static::$slug] == $row->id)
      {
        $this->RenderEditRow($row);
        $this->RenderEditRowEnd($row);
      }
      else if ($_GET["del" . static::$slug] == $row->id && $this->allowDelete)
      {
        $this->RenderNormalRow($row);
        $this->RenderDeleteRowEnd($row);
      }
      else
      {
        $this->RenderNormalRow($row);
        $this->RenderNormalRowEnd($row);
      }
      echo "  </tr>\n";
    }
    if ($_GET["new" . static::$slug])
    {
      $this->RenderEditRow( new stdClass() );
      $this->RenderEditRowEnd( new stdClass() );
    }
    echo "</table>\n";
    echo "<div class='foot'>";
    printf("<a href='%s?which=%d&amp;new%s=true' class='new'>new</a>",$_SERVER["SCRIPT_NAME"],$this->id,static::$slug);
    echo "</div>\n";
  }
}
?>