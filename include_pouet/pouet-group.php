<?php
class PouetGroup extends BM_Class
{
  static function getTable () { return "groups"; }
  static function getFields() { return array("id","name","acronym","disambiguation","web","addedUser","addedDate"); }
  static function getExtendedFields() { return array("csdb","zxdemo","demozoo"); }

  static function onAttach( &$node, &$query )
  {
  }

  function RenderShort()
  {
    if ($this->acronym && strlen($this->name)>15)
    {
      return sprintf("<a href='groups.php?which=%d'><abbr title='%s'>%s</abbr></a>", $this->id, _html( $this->name ), _html( $this->acronym ) );
    }
    return $this->RenderLong();
  }
  function RenderLong()
  {
    return sprintf("<a href='groups.php?which=%d'>%s</a>",
      $this->id, _html( $this->name ) );
  }
  function RenderFull()
  {
    $s = sprintf("<a href='groups.php?which=%d'>%s</a>",$this->id,_html($this->name));
    if ($this->web)
      $s .= sprintf(" [<a href='%s'>web</a>]",_html($this->web));
    return $s;
  }

  use PouetAPI;
};

BM_AddClass("PouetGroup");
?>
