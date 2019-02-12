<?php

class Sideload
{
  function RequestCURL( $url )
  {
    $curl = curl_init();
  
    $header = array();
  
    curl_setopt($curl, CURLOPT_URL, $url);
    @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if ($this->options["connect_timeout"])
      curl_setopt($curl, CURLOPT_TIMEOUT, (int)$this->options["connect_timeout"]);
    if ($this->options["user_agent"])
      curl_setopt($curl, CURLOPT_USERAGENT, $this->options["user_agent"]);
    if ($this->options["max_length"])
    {
      $maxlen = $this->options["max_length"];
      $dataLength = 0;
      curl_setopt($curl, CURLOPT_WRITEFUNCTION, function($ch,$data)use($maxlen,$dataLength){
        $length = strlen($data);
        $dataLength += $length;
        return ($dataLength < $maxlen) ? $length : 0;
      });
    }
    curl_setopt($curl, CURLOPT_NOPROGRESS, true);
    if ($this->options["verify_peer"])
    {
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->options["verify_peer"]);
    }
    if ($this->options["method"] == "GET")
    {
      curl_setopt($curl, CURLOPT_HTTPGET, true);    
    }
  
    $html = curl_exec($curl);
    
    $this->httpReturnCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    $this->httpReturnContentType = curl_getinfo($curl,CURLINFO_CONTENT_TYPE);
    $this->httpURL = curl_getinfo($curl,CURLINFO_EFFECTIVE_URL);
    
    curl_close($curl);
  
    return $html;
  }
  function RequestFGC( $url )
  {
    $opt = array();
    $param = array();
    if ($this->options["connect_timeout"])
    {
      $opt["http"]["timeout"] = $this->options["connect_timeout"];
    }
    if ($this->options["method"])
    {
      $opt["http"]["method"] = $this->options["method"];
    }
    if ($this->options["verify_peer"])
    {
      $opt["ssl"]["verify_peer"] = $this->options["verify_peer"];
    }
    if ($this->options["user_agent"])
    {
      $opt["ssl"]["user_agent"] = $this->options["user_agent"];
    }
    $ctx = stream_context_create($opt);
    if ($this->options["max_length"])
    {
      $data = @file_get_contents($url, false, $ctx, 0, $this->options["max_length"]);
    }
    else
    {
      $data = @file_get_contents($url, false, $ctx);
    }

    if (strstr($url,"http://")!==false || strstr($url,"https://")!==false)
    {
      $this->httpReturnCode = 0;
      $this->httpReturnContentType = "";
      if ($http_response_header)
      { 
        foreach($http_response_header as $header)
        {
          if (preg_match('/HTTP\/.*\s(\d+)/', $header, $match))
          {
            $this->httpReturnCode = (int)$match[1];
          }
          if (preg_match('/Content-type: (.*)/i', $header, $match))
          {
            $this->httpReturnContentType = $match[1];
          }
        }
      }
    }
    else
    {
      $this->httpReturnCode = $data === false ? 550 : 150;
      $this->httpReturnContentType = "";
    }
    $this->httpURL = $url;
    
    return $data;
  }
  function Request( $url )
  {
    if (function_exists("curl_init"))
    {
      return $this->RequestCURL($url);
    }
    else
    {
      return $this->RequestFGC($url);
    }
  }
}
?>