<?php
class PouetBoard extends BM_Class 
{
  public $id;
  public $name;
  public $sysop;
  public $phonenumber;
  public $addedDate;
  public $addedUser;
  public $telnetip;
  public $started;
  public $closed;

  static function getTable () { return "boards"; }
  static function getFields() { return array("id","name","addedDate","addedUser"); }
  static function getExtendedFields() { return array("sysop","phonenumber"); }
  static function onAttach( &$node, &$query )
  {
    $node->attach( $query, "addedUser", array("users as addedUser"=>"id"));
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