<?
class PouetBoard extends BM_Class {
  static function getTable () { return "boards"; }
  static function getFields() { return array("id","name"); }
  static function onAttach( &$node, &$query )
  {
  }
  function RenderLink() {
    return sprintf("<a href='boards.php?which=%d'>%s</a>",$this->id,_html($this->name));
  }
};

BM_AddClass("PouetBoard");
?>