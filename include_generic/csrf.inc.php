<?
class CSRFProtect 
{
  public function CSRFProtect()
  {
    if(session_id() == '')
      die("Initialize sessions please!");
    
    if ($_SESSION["CSRFProtect"])
    {
      // garbage collect
      foreach($_SESSION["CSRFProtect"] as $k=>$v)
      {
        if ($v["time"] > time() + 60 * 60 * 24)
        {
          unset( $_SESSION["CSRFProtect"][$k] );
        }
      }
    }
  }
  public function PrintToken()
  {
    //do {
      $name  = "Protect".sprintf("%06d",rand(0,999999));
    //} while (isset($_SESSION["CSRFProtect"][$name]));
    
    $token = sha1(time() . rand(0,9999));
    printf("<input type='hidden' name='ProtName' value='%s'/>\n",_html($name));
    printf("<input type='hidden' name='ProtValue' value='%s'/>\n",_html($token));
    $_SESSION["CSRFProtect"][$name]["token"] = $token;
    $_SESSION["CSRFProtect"][$name]["time"] = time();
  }
  public function ValidateToken()
  {
    if ($_SESSION["CSRFProtect"][ $_POST["ProtName"] ] && $_SESSION["CSRFProtect"][ $_POST["ProtName"] ]["token"] == $_POST["ProtValue"])
    {
      unset($_SESSION["CSRFProtect"][ $_POST["ProtName"] ]);
      return true;
    }
    return false;
  }
}
?>