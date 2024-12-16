<?php
class PouetGroup extends BM_Class
{
  public $id;
  public $name;
  public $acronym;
  public $disambiguation;
  public $web;
  public $addedUser;
  public $addedDate;
  public $csdb;
  public $zxdemo;
  public $demozoo;

  static function getTable () { return "groups"; }
  static function getFields() { return array("id","name","acronym","disambiguation","web","addedUser","addedDate"); }
  static function getExtendedFields() { return array("csdb","zxdemo","demozoo"); }

  static function onAttach( &$node, &$query )
  {
  }
  
  function Delete()
  {
    global $currentUser;
    if (!($currentUser && $currentUser->CanDeleteItems()))
      return;

    SQLLib::Query(sprintf_esc("UPDATE prods SET group1=NULL WHERE group1=%d",$this->id));
    SQLLib::Query(sprintf_esc("UPDATE prods SET group2=NULL WHERE group2=%d",$this->id));
    SQLLib::Query(sprintf_esc("UPDATE prods SET group3=NULL WHERE group3=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM groupsaka WHERE group1=%d OR group2=%d",$this->id,$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM affiliatedboards WHERE `group`=%d",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM list_items WHERE itemid=%d AND type='group'",$this->id));
    SQLLib::Query(sprintf_esc("DELETE FROM groups WHERE id=%d",$this->id));
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
