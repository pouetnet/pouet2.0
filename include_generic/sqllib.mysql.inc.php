<?php
require_once('credentials.php');

$SQLLIB_QUERIES = 0;

$SQLLIB_ARRAYS_CLEANED = false;

class SQLLib {

  function Connect() {
    mysql_connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD);
    mysql_select_db(SQL_DATABASE);
  }

  function Disconnect() {
    mysql_close();
  }

  function Query($cmd) {
    global $SQLLIB_QUERIES;

    $r = @mysql_query($cmd);
    if(!$r) die("<pre>\nMySQL ERROR:\nQuery: ".$cmd."\nError: ".mysql_error());

    $SQLLIB_QUERIES++;

    return $r;
  }

  function SelectRows($cmd) {
    $r = SQLLib::Query($cmd);
    $a = Array();
    while($o = mysql_fetch_object($r)) $a[]=$o;
    return $a;
  }

  function SelectRow($cmd) {
    $r = SQLLib::Query($cmd);
    $a = mysql_fetch_object($r);
    return $a;
  }

  function InsertRow($table,$o) {
    global $SQLLIB_ARRAYS_CLEANED;
    if (!$SQLLIB_ARRAYS_CLEANED)
      trigger_error("Arrays not cleaned before InsertRow!",E_USER_ERROR);

    if (is_object($o)) $a = get_object_vars($o);
    else if (is_array($o)) $a = $o;
    $keys = Array();
    $values = Array();
    foreach($a as $k=>$v) {
      $keys[]="`".$k."`";
      if ($v!==NULL) $values[]="'".mysql_real_escape_string($v)."'";
      else           $values[]="''";
    }

    $cmd = sprintf("insert %s (%s) values (%s)",
      $table,implode(", ",$keys),implode(", ",$values));

    $r = SQLLib::Query($cmd);

    return mysql_insert_id();
  }

  function UpdateRow($table,$o,$where) {
    global $SQLLIB_ARRAYS_CLEANED;
    if (!$SQLLIB_ARRAYS_CLEANED)
      trigger_error("Arrays not cleaned before UpdateRow!",E_USER_ERROR);

    if (is_object($o)) $a = get_object_vars($o);
    else if (is_array($o)) $a = $o;
    $set = Array();
    foreach($a as $k=>$v) {
      if ($v!==NULL)
        $set[] = sprintf("`%s`='%s'",$k,mysql_real_escape_string($v));
    }
    $cmd = sprintf("update %s set %s where %s",
      $table,implode(", ",$set),$where);
    SQLLib::Query($cmd);
  }

}

class SQLSelect {
  var $fields;
  var $tables;
  var $conditions;
  var $joins;
  var $orders;
  var $groups;

  function SQLSelect() {
    $this->fields = array();
    $this->tables = array();
    $this->conditions = array();
    $this->joins = array();
    $this->orders = array();
    $this->groups = array();
  }
  function AddTable($s) {
    $this->tables[] = $s;
  }
  function AddField($s) {
    $this->fields[] = $s;
  }
  function AddJoin($type,$table,$condition) {
    $o = NULL;
    $o->type = $type;
    $o->table = $table;
    $o->condition = $condition;
    $this->joins[] = $o;
  }
  function AddWhere($s) {
    $this->conditions[] = $s;
  }
  function AddOrder($s) {
    $this->orders[] = $s;
  }
  function AddGroup($s) {
    $this->groups[] = $s;
  }
  function SetLimit($s) {
    $this->limit = $s;
  }
  function GetQuery() {
    $sql = "SELECT ";
    if ($this->fields) {
      $sql .= implode(",",$this->fields);
    } else {
      $sql .= " * ";
    }
    $sql .= " FROM ".implode(",",$this->tables);
    if ($this->joins) {
      foreach ($this->joins as $v) {
        $sql .= " ".$v->type." JOIN ".$v->table." ON ".$v->condition;
      }
    }
    if ($this->conditions)
      $sql .= " WHERE ".implode(" AND ",$this->conditions);
    if ($this->groups)
      $sql .= " GROUP BY ".implode(",",$this->groups);
    if ($this->orders)
      $sql .= " ORDER BY ".implode(",",$this->orders);
    if ($this->limit)
      $sql .= " LIMIT ".$this->limit;
    return $sql;
  }
}

function sprintf_esc() {
  $args = func_get_args();
  reset($args);
  next($args);
  while (list($key, $value) = each($args))
    $args[$key] = mysql_real_escape_string( $args[$key] );

  return call_user_func_array("sprintf", $args);
}

function nop($s) { return $s; }
function clearArray($a) {
  $ar = array();
  $qcb = get_magic_quotes_gpc() ? "stripslashes" : "nop";
  foreach ($a as $k=>$v)
    if (is_array($v))
      $ar[$k] = clearArray($v);
    else
      $ar[$k] = $qcb($v);
  return $ar;
}

$_POST = clearArray($_POST);
$_GET = clearArray($_GET);
$_REQUEST = clearArray($_REQUEST);
$SQLLIB_ARRAYS_CLEANED = true;
SQLLib::Connect();
?>
