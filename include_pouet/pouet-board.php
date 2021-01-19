<?php
class PouetBoard extends BM_Class {
  static function getTable () { return "boards"; }
  static function getFields() { return array("id","name"); }
  static function getExtendedFields() { return array("sysop","phonenumber","addedDate","addedUser"); }
  static function onAttach( &$node, &$query )
  {
    $node->attach( $query, "addedUser", array("users as addeduser"=>"id"));
  }
  function RenderLink() {
    return sprintf("<a href='boards.php?which=%d'>%s</a>",$this->id,_html($this->name));
  }

  use PouetAPI { ToAPI as protected ToAPISuper; }

  function ToAPI()
  {
    $array = $this->ToAPISuper();
    unset($array["addedUser"]);
    return $array;
  }
};

BM_AddClass("PouetBoard");
?>