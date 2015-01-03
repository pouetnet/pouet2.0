<?
interface IEphemeralStorage
{
  function get($key);
  function set($key,$value);
  function has($key);
}

class SessionStorage implements IEphemeralStorage
{
  function __construct()
  {
    //@session_start();
  }
  function get($key)
  {
    return $_SESSION["ES:".$key];
  }
  function set($key,$value);
  {
    $_SESSION["ES:".$key] = $value;
  }
  function has($key)
  {
    return isset($_SESSION["ES:".$key]);
  }
}

class RedisStorage implements IEphemeralStorage
{
  function __construct()
  {
    $this->redis = new Redis("localhost");
  }
  function get($key)
  {
    return $this->redis->get("ES:".$key);
  }
  function set($key,$value);
  {
    $this->redis->set("ES:".$key, $value);
  }
  function has($key)
  {
    return $this->redis->exists("ES:".$key);
  }
}

// add memcached on demand

$ephemeralStorage = null;
if (class_exists("Redis"))
  $ephemeralStorage = new RedisStorage();
else
  $ephemeralStorage = new SessionStorage();
?>