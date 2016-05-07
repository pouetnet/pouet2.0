<?
class PouetBoard extends BM_Class {
  static function getTable () { return "boards"; }
  static function getFields() { return array("id","name"); }
  static function onAttach( &$node, &$query )
  {
  }
};

BM_AddClass("PouetBoard");
?>