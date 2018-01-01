<?php

trait PouetAPI
{
  static function Apify($o)
  {
    if (is_object($o))
    {
      if (has_trait($o,"PouetAPI"))
      {
        return $o->ToAPI();
      }
      else
      {
        foreach(get_object_vars($o) as $k=>$v)
        {
          $o->$k = PouetAPI::Apify($o->$k);
        }
      }
    }
    else if (is_array($o))
    {
      foreach($o as $k=>$v)
      {
        $o[$k] = PouetAPI::Apify($o[$k]);
      }
    }
    return $o;
  }
  function ToAPI()
  {
    $array = get_object_vars($this);
    foreach($array as $k=>$v)
    {
      $array[$k] = PouetAPI::Apify($this->$k);
    }
    return $array;
  }
}

?>