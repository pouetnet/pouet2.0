<?php
class Rewriter
{
  private $rules = array();
  private $bootstrap = array();
  private $entryPoints = array();
  function addRules( $arr )
  {
    if (!is_array($arr))
      return;
    $this->rules = array_merge( $this->rules, $arr );
  }
  function addBootstrap( $file )
  {
    if (!file_exists($file))
      return;
    $this->bootstrap[] = $file;
  }
  function addEntryPoint( $symbol )
  {
    $this->entryPoints[] = $symbol;
  }
  function rewrite()
  {
    foreach($this->entryPoints as $v)
      define($v,true);
    
    $dir = dirname($_SERVER["PHP_SELF"]);
    
    $url = $_SERVER['REQUEST_URI'];
    if (strlen($dir) > 1)
      $url = substr($url,strlen($dir));
    
    $url_a = parse_url($url);
    
    /*
     * Rewrite rules for the entire site - standard regexp
     */
    
    /*
     * Match regexp to current path
     */
    
    $newURL = "";
    foreach($this->rules as $k=>$v) {
      if (preg_match("/".$k."/",$url_a["path"])) {
        $newURL = preg_replace("/".$k."/", $v, $url_a["path"]);
      }
    }
    
    
    if ($newURL) {
      $newGET = array();
      parse_str(@$url_a["query"]?:"",$newGET);
    
      $url_a = parse_url($newURL);
      parse_str(@$url_a["query"]?:"",$_GET);
    
      $localPath = trim($url_a["path"],"./");
    
      /*
       * Merge existing GET parameters with the ones we won from the regexp
       */
    
      $_GET = array_merge($newGET, $_GET);
      
      if (!file_exists($localPath))
      {
        header("HTTP/1.1 500 Internal Server Error");
        die("<html><body>HTTP 500 - Internal Server Error</body></html>");
        exit();  
      }
      //include_once("./bootstrap.inc.php");
      foreach($this->bootstrap as $v)
        include_once($v);
      include_once("./" . $localPath);
    } else {
      
      /*
       * If no match, throw 404
       */
      
      header("HTTP/1.1 404 Not Found");
      die("<html><body>HTTP 404 - Not Found</body></html>");
      exit();  
    }
  }
}
?>