<?php

class PouetRequest_Group_ChangeInfo extends PouetRequestBase
{
  static function GetItemType() { return "group"; }
  static function Describe() { return "change group info"; }

  static function GetFields($data,&$fields,&$js)
  {
    $fields = array(
      "name"=>array(
        "name"=>"group name",
        "required"=>true,
      ),
      "acronym"=>array(
        "name"=>"acronym",
        "html"=>"maxlength='8'",
      ),
      "web"=>array(
        "name"=>"website url",
        "type"=>"url",
      ),
      "disambiguation"=>array(
        "name"=>"disambiguation",
        "infoAfter"=>"If there are a multiple groups with the same name, add distinguishing platforms or countries here, otherwise leave it empty.",
      ),
      "csdb"=>array(
        "name"=>"csdb ID",
        "type"=>"number",
      ),
      "demozoo"=>array(
        "name"=>"demozoo ID",
        "type"=>"number",
      ),

      // Add this if all the other data is collected
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );
    
    if (@$data["group"] && $group = PouetGroup::Spawn( $data["group"] ))
    {
      foreach(get_object_vars($group) as $k=>$v)
      {
        if (@$fields[$k])
        {
          $fields[$k]["value"] = $v;    
        }
      }
    }
  }

  static function ValidateRequest($input,&$output)
  {
    if (!trim($input["name"]))
    {
      return array("Whitespace groupnames are sooo \t       \t        \t.");
    }
    if (@$input["website"])
    {
      $url = parse_url($input["website"]);
      if (($url["scheme"]!="http" && $url["scheme"]!="https") || strstr($data["website"],"://")===false)
        return array("please only websites with http or https links, kthx");
    }
        
    $group = PouetGroup::Spawn( $_REQUEST["group"] );
    
    $fields = array();
    $js = "";
    static::GetFields(array(),$fields,$js);

    $_in = array();
    foreach($fields as $k=>$v) $_in[$k] = $input[$k];
    $output = array_diff_meaningful( $_in, get_object_vars($group) );
    
    unset($output["finalStep"]);
    
    return $output ? array() : array("you didn't change anything !");
  }

  static function Display($itemID, $data)
  {
    $fields = array();
    $js = "";
    static::GetFields(array(),$fields,$js);

    $group = PouetGroup::Spawn( $itemID );
    
    $s = "";
    foreach($data as $k=>$v)
    {
      switch($k)
      {
        default:
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= _html($group->{$k});
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= _html($v);
          $s .= "<br/>";    
          break;
      }
    }
    
    return $s;
  }

  static function Process($itemID, $reqData)
  {
    $sql = array();
    foreach($reqData as $k=>$v)
    {
      $sql[$k] = $v;
    }
    if ($sql)
    {
      SQLLib::UpdateRow("groups",$sql,"id=".(int)$itemID);
    }

    return array();
  }
};

///////////////////////////////////////////////////////////////////////////////

$REQUESTTYPES["group_change_info"] = "PouetRequest_Group_ChangeInfo";
?>