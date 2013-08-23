<?
class PouetRequestClassBase 
{
  // return the type of atomic item this request handles (can be "prod", "group" or "party")
  static function GetItemType() { return ""; }

  // return human-readable description of operation
  static function Describe() { return ""; }
  
  // return error string on error, empty string / null / false / etc. on success
  static function GetFields($data,&$fields) { return ""; }

  // return HTML string
  static function Display($data) { return ""; }

  // return error string on error, empty string / null / false / etc. on success
  static function Process($itemID,$reqData) { return ""; }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassAddLink extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "add a new extra link to a prod"; }
  
  static function GetFields($data,&$fields) 
  {
    $fields = array(
      "newLinkKey" => array(
        "name"=>"link description (youtube, source, linux port, etc)",
      ),
      "newLink" => array(
        "name"=>"link url",
        "type"=>"url",
      ),
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );
  }

  static function Display($data) 
  { 
    $s =  _html($data["newLinkKey"])." - ";
    $s .= "<a href='"._html($data["newLink"])."'>"._html(shortify_cut($data["newLink"],50))."</a>";
    return $s;
  }

  static function Process($itemID, $reqData) 
  { 
    $a = array();
    $a["prod"] = $itemID;
    $a["type"] = $reqData["newLinkKey"];
    $a["link"] = $reqData["newLink"];
    SQLLib::InsertRow("downloadlinks",$a);
  }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassChangeLink extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "change an existing extra link"; }
  
  static function GetFields($data,&$fields) 
  {
    if ($data["linkID"])
    {
      $l = SQLLib::SelectRow(sprintf_esc("select * from downloadlinks where id = %d",$data["linkID"]));
      $fields = array(
        "linkID" => array(
          "type"=>"hidden",
          "value"=>(int)$data["linkID"],
        ),
        "newLinkKey" => array(
          "name"=>"link description (youtube, source, linux port, etc)",
          "type"=>"text",
          "value"=>$l->type,
        ),
        "newLink" => array(
          "name"=>"link url",
          "type"=>"url",
          "value"=>$l->link,
        ),
        "finalStep" => array(
          "type"=>"hidden",
          "value"=>1,
        ),
      );
    }
    else
    {
      $l = SQLLib::SelectRows(sprintf_esc("select * from downloadlinks where prod = %d",$data["prod"]));
      foreach($l as $v)
        $links[$v->id] = sprintf("[%s] %s",$v->type,$v->link);
      if (!$links)
        return "this prod has no extra links to change !";
      $fields = array(
        "linkID" => array(
          "name"=>"select link you want to edit",
          "type"=>"select",
          "fields"=>$links,
          "assoc"=>true,
        ),
      );
    }
  }

  static function Display($data) 
  { 
    $row = SQLLib::selectRow(sprintf_esc("select * from downloadlinks where id = %d",$data["linkID"]));
    $s = "<b>old</b>: ";
    $s .= _html($row->type)." - ";
    $s .= "<a href='"._html($row->link)."'>"._html(shortify_cut($row->link,50))."</a>";
    $s .= "<br/><b>new</b>: ";
    $s .= _html($data["newLinkKey"])." - ";
    $s .= "<a href='"._html($data["newLink"])."'>"._html(shortify_cut($data["newLink"],50))."</a>";
    return $s;
  }

  static function Process($itemID, $reqData) 
  {
    $a = array();
    $a["type"] = $reqData["newLinkKey"];
    $a["link"] = $reqData["newLink"];
    SQLLib::UpdateRow("downloadlinks",$a,"id=".(int)$reqData["linkID"]);
  }
};


///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassRemoveLink extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "remove an existing extra link"; }
  
  static function GetFields($data,&$fields) 
  {
    $l = SQLLib::SelectRows(sprintf_esc("select * from downloadlinks where prod = %d",$data["prod"]));
    foreach($l as $v)
      $links[$v->id] = sprintf("[%s] %s",$v->type,$v->link);
      
    if (!$links)
      return "this prod has no extra links to remove !";
      
    $fields = array(
      "linkID" => array(
        "name"=>"select link you want removed",
        "type"=>"select",
        "fields"=>$links,
        "assoc"=>true,
      ),
      "reason" => array(
        "name"=>"why should this link be deleted",
        "type"=>"textarea",
        "info"=>"moderator's note: abuse of this feature will result in retaliation. have a nice day.",
      ),
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );
  }

  static function Display($data) 
  { 
    $row = SQLLib::selectRow(sprintf_esc("select * from downloadlinks where id = %d",$data["linkID"]));
    $s = _html($row->type)." - ";
    $s .= "<a href='"._html($row->link)."'>"._html(shortify_cut($row->link,50))."</a>";
    $s .= "<br/><b>reason</b>: ";
    $s .= _html($data["reason"]);
    return $s;
  }

  static function Process($itemID, $reqData) 
  { 
    SQLLib::Query(sprintf_esc("delete from downloadlinks where id=%d",$reqData["linkID"]));
  }
};

$REQUESTTYPES = array(
  "prod_add_link" => "PouetRequestClassAddLink",
  "prod_change_link" => "PouetRequestClassChangeLink",
  "prod_remove_link" => "PouetRequestClassRemoveLink",
  //"prod_change_field" => "change basic info about a prod",
  //"prod_del" => "delete a prod",
);
?>