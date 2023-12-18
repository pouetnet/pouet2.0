<?php
interface IEphemeralStorage
{
  static function available();
  function get($key);
  function set($key,$value);
  function has($key);
}
define("EPHEMERALSTORAGE_PREFIX","POUET:EPHSTOR:");

// NOTE:
// This is NOT a good replacement for NoSQL storages
// This is just a fallback!
class SessionStorage implements IEphemeralStorage
{
  static function available()
  {
    return true;
  }
  function __construct()
  {
    //@session_start();
  }
  function get($key)
  {
    return $_SESSION[EPHEMERALSTORAGE_PREFIX.$key];
  }
  function set($key,$value)
  {
    $_SESSION[EPHEMERALSTORAGE_PREFIX.$key] = $value;
  }
  function has($key)
  {
    return isset($_SESSION[EPHEMERALSTORAGE_PREFIX.$key]);
  }
}

// this is better but it's brutally slow :)
class FileStorage implements IEphemeralStorage
{
  public $dir;
  static function available()
  {
    return true;
  }
  function __construct()
  {
    $this->dir = "/tmp/ephstor/";
    @mkdir($this->dir);
  }
  function keyToFilename($key)
  {
    $s =  $this->dir . EPHEMERALSTORAGE_PREFIX . $key;
    return str_replace(":","_",$s);
  }
  function get($key)
  {
    return unserialize( file_get_contents( $this->keyToFilename($key) ) );
  }
  function set($key,$value)
  {
    @file_put_contents( $this->keyToFilename($key), serialize($value) );
  }
  function has($key)
  {
    return file_exists( $this->keyToFilename($key) );
  }
}

class RedisStorage implements IEphemeralStorage
{
  public $redis;
  static function available()
  {
    return class_exists("Redis");
  }
  function __construct()
  {
    $this->redis = new Redis();
    $this->redis->connect('localhost');
  }
  function get($key)
  {
    return unserialize($this->redis->get(EPHEMERALSTORAGE_PREFIX.$key));
  }
  function set($key,$value)
  {
    $this->redis->set(EPHEMERALSTORAGE_PREFIX.$key, serialize($value));
  }
  function has($key)
  {
    return $this->redis->exists(EPHEMERALSTORAGE_PREFIX.$key);
  }
}

// add memcached on demand

$ephemeralStorage = NULL;
foreach(array(
  "RedisStorage",
  "FileStorage",
  "SessionStorage",
  ) as $cls)
{
  if ($cls::available())
  {
    $ephemeralStorage = new $cls();
    break;
  }
}
?>
