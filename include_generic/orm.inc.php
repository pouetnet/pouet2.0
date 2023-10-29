<?php
//require_once("credentials.php");
require_once("sqllib.inc.php");

$BM_ORM_CLASSES = array();

function BM_AddClass( $class )
{
  global $BM_ORM_CLASSES;
  $BM_ORM_CLASSES[ $class::getTable() ] = $class;
}

class BM_Node 
{
  public $id;
  public $table;
  public $class;
  public $attachments;
  public $prefix;
  public $fieldFunc;
  function __construct( &$query, $a = array() )
  {
    global $BM_ORM_CLASSES;
    $this->attachments = array();
    foreach($a as $k=>$v)
      $this->$k = $v;
    if ($this->table)
      $this->class = $BM_ORM_CLASSES[$this->table];

    if ($this->table && $BM_ORM_CLASSES[$this->table])
    {
      $func = $this->fieldFunc ? $this->fieldFunc : "getFields";
      foreach ($BM_ORM_CLASSES[$this->table]::$func() as $v)
      {
        $query->AddField($this->getTableInstanceName() . "." . $v . " as " . ($this->getTableInstanceName() ? $this->getTableInstanceName() . "_" : "") . $v);
      }
    }
  }
  function &attach( &$query, $local, $relations, $additionalCondition = null )
  {
    global $BM_ORM_CLASSES;

    $condition = "";

    list($relationTable) = array_keys( $relations );
    list($relationField) = array_values( $relations );

    list($relationTableCanonical,$relationTableAlias) = preg_split("/\s+as\s+/i",$relationTable);
    if (!$relationTableAlias) $relationTableAlias = $relationTableCanonical;

    if (is_array($local))
    {
      list($localTable) = array_keys( $local );
      list($localField) = array_values( $local );
    }
    else
    {
      $localTable = $this->getTableInstanceName();
      $localField = $local;
    }

    $prefix = $localTable . "_" . $relationTableAlias;

    $query->AddJoin("LEFT",$relationTableCanonical . " AS " . $prefix, sprintf("%s.%s = %s.%s",$localTable,$localField,$prefix,$relationField) . ($additionalCondition?" AND ".$additionalCondition:"") );

    $node = new BM_Node( $query, array("id"=>$relationTableAlias,"table"=>$relationTableCanonical,"prefix"=>$prefix) );
    $this->attachments[] = $node;

    if ($BM_ORM_CLASSES[$relationTableCanonical])
      $BM_ORM_CLASSES[$relationTableCanonical]::onAttach($node,$query);

    return $node;
  }
  function getTableInstanceName()
  {
    return $this->prefix ? $this->prefix : $this->table;
  }
  function addExtendedFields( &$query )
  {
    $class = $this->class;
    foreach($class::getExtendedFields() as $v)
      $query->AddField($this->getTableInstanceName().".".$v);
  }
};

class BM_Query extends SQLSelect 
{
  public $root;
  function __construct($table = null)
  {
    global $BM_ORM_CLASSES;
    $this->root = array();
    if ($table)
    {
      $this->root = new BM_Node( $this, array("table"=>$table) );
      if ($BM_ORM_CLASSES[$table])
        $BM_ORM_CLASSES[$table]::onAttach($this->root,$this);
      $this->AddTable($table);
    }
    else
    {
      $this->root = new BM_Node( $this );
    }
  }
  function &attach( $local, $relations, $additionalCondition = null )
  {
    $v = &$this->root->attach( $this, $local, $relations, $additionalCondition );
    return $v; // has to be in two lines, php parser is shit
  }
  function addExtendedFields()
  {
    $this->root->addExtendedFields($this);
  }
  function populate(&$object, $node, &$row)
  {
    $isValid = false;
    if ($node->class)
    {
      $class = $node->class;

      $keyFieldName = ($node->getTableInstanceName() ? $node->getTableInstanceName() . "_" : "") . $class::getPrimaryKey();

      if ($row->{$keyFieldName} !== null)
      {
        $object = new $class();
        $func = $node->fieldFunc ? $node->fieldFunc : "getFields";
        foreach($class::$func() as $v)
        {
          $fieldName = ($node->getTableInstanceName() ? $node->getTableInstanceName() . "_" : "") . $v;
          $object->$v = $row->{$fieldName};
          unset( $row->{$fieldName} );
        }
        if ($node->attachments)
        {
          foreach($node->attachments as $v)
          {
            $this->populate($object->{$v->id}, $v, $row);
          }
        }
        $object->onFinishedPopulate();
      }
      else
      {
        $func = $node->fieldFunc ? $node->fieldFunc : "getFields";
        foreach($class::$func() as $v)
        {
          $fieldName = ($node->getTableInstanceName() ? $node->getTableInstanceName() . "_" : "") . $v;
          unset( $row->{$fieldName} );
        }
      }
    }
    else
    {
      if ($node->attachments)
      {
        foreach($node->attachments as $v)
        {
          $this->populate($object->{$v->id}, $v, $row);
        }
      }
    }
    return $isValid;
  }
  function perform() // "'perform'?! in germany, queries get EXECUTED!" /dfox/
  {
    $objects = array();
    $sql = $this->GetQuery();
    $rows = SQLLib::selectRows( $sql );
    foreach($rows as $row)
    {
      $object = new stdClass;
      $this->populate( $object, $this->root, $row );
      foreach(get_object_vars($row) as $k=>$v)
      {
        $object->$k = $v;
      }
      if ($this->root->class)
      {
        $class = $this->root->class;
        $field = $class::getPrimaryKey();
        $objects[ $object->$field ] = $object;
      }
      else
      {
        $objects[] = $object;
      }
    }
    return $objects;
  }
  function performWithCalcRows( &$count )
  {
    $objects = array();
    $sql = $this->GetQuery();
    $sql = preg_replace("/^SELECT/","SELECT SQL_CALC_FOUND_ROWS ",$sql);
    $rows = SQLLib::selectRows( $sql );
    foreach($rows as $row)
    {
      $object = new stdClass;
      $this->populate( $object, $this->root, $row );
      foreach(get_object_vars($row) as $k=>$v)
      {
        $object->$k = $v;
      }
      if ($this->root->class)
      {
        $class = $this->root->class;
        $field = $class::getPrimaryKey();
        $objects[ $object->$field ] = $object;
      }
      else
      {
        $objects[] = $object;
      }
    }

    $row = SQLLib::selectRow("SELECT FOUND_ROWS() as f");
    $count = (int)$row->f;

    return $objects;
  }

};

#[AllowDynamicProperties]
class BM_Class 
{
  static function getTable () { trigger_error("GetTable not overridden in ".get_class(),E_USER_ERROR); }
  static function getFields() { return array("id"); }
  static function getExtendedFields() { return array_merge(static::getFields(),array()); }
  static function getPrimaryKey() { return "id"; }
  static function onAttach( &$node, &$query ) {}
  function onFinishedPopulate() {}

  static function spawn( $id )
  {
    $query = new BM_Query( static::getTable() );
    $query->addExtendedFields();
    $query->AddWhere(sprintf_esc("%s.%s = %d",static::getTable(),static::getPrimaryKey(),(int)$id));
    $query->SetLimit(1);
    $rows = $query->perform();
    return reset($rows);
  }
}
?>
