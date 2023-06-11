<?php
class PouetRequestBase
{
  // return the type of atomic item this request handles (can be "prod", "group" or "party")
  static function GetItemType() { return ""; }

  // return human-readable description of operation
  static function Describe() { return ""; }

  // return error string on error, empty string / null / false / etc. on success
  static function GetFields($data,&$fields,&$js) { return ""; }

  // transform form $input into sql-ish $output
  // return error array on error, empty array on success
  static function ValidateRequest($input,&$output) { $output = $input; return array(); }

  // return HTML string describing the changes
  // - $data is the changeset
  static function Display($itemID, $data) { return ""; }

  // commit changeset
  // - $reqData is the changeset
  // return error array on error, empty array on success
  static function Process($itemID,$reqData) { return array(); }
};

/*
A typical request class would look would something like this:

class PouetRequestClassDoStuffToProds extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "Do stuff to prods"; }

  static function GetFields($data,&$fields,&$js)
  {
    $fields = array(
      "stuff" => array(
        "name"=>"the stuff to be done",
        "type"=>"textarea",
        "info"=>"good stuff. yeah.",
        "required"=>true,
      ),
      
      // Add this if all the other data is collected
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );
  }

  static function ValidateRequest($input,&$output)
  {
    if($input["stuff"] != "good")
    {
      return array("stuff not good !");
    }
    
    $output["stuff"] = $input["stuff"];
    return array();
  }

  static function Display($itemID, $data)
  {
    $s .= _html($data["stuff"]);

    return $s;
  }

  static function Process($itemID, $reqData)
  {
    if (!CommitStuffToProd($itemID, $reqData["stuff"]))
    {
      return array("stuff failed !");
    }

    return array();
  }
};

*/

$REQUESTTYPES = array();
?>