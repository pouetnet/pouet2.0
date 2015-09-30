<?
class PouetRequestClassBase
{
  // return the type of atomic item this request handles (can be "prod", "group" or "party")
  static function GetItemType() { return ""; }

  // return human-readable description of operation
  static function Describe() { return ""; }

  // return error string on error, empty string / null / false / etc. on success
  static function GetFields($data,&$fields,&$js) { return ""; }

  // return error array on error, empty array on success
  static function ValidateRequest($input,&$output) { $output = $input; return array(); }

  // return HTML string
  static function Display($itemID, $data) { return ""; }

  // return error array on error, empty array on success
  static function Process($itemID,$reqData) { return array(); }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassAddLink extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "add a new extra link to a prod"; }

  static function GetFields($data,&$fields,&$js)
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

  static function ValidateRequest($input,&$output)
  {
    $errors = validateLink($input["newLink"]);
    if ($errors)
      return $errors;

    $output["newLink"] = $input["newLink"];
    $output["newLinkKey"] = $input["newLinkKey"];
    return array();
  }

  static function Display($itemID, $data)
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
    return array();
  }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassChangeLink extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "change an existing extra link"; }

  static function GetFields($data,&$fields,&$js)
  {
    if ($data["linkID"])
    {
      $l = SQLLib::SelectRow(sprintf_esc("select * from downloadlinks where id = %d",$data["linkID"]));
      $fields = array(
        "linkID" => array(
          "type"=>"hidden",
          "value"=>(int)$data["linkID"],
        ),
        "oldLinkKey" => array(
          "type"=>"hidden",
          "value"=>$l->type,
        ),
        "oldLink" => array(
          "type"=>"hidden",
          "value"=>$l->link,
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
        "reason" => array(
          "name"=>"why should this link be changed",
          "type"=>"textarea",
          "info"=>"moderator's note: abuse of this feature will result in retaliation. have a nice day.",
          "required"=>true,
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

  static function ValidateRequest($input,&$output)
  {
    $errors = validateLink($input["newLink"]);
    if ($errors)
      return $errors;

    if (!$input["reason"])
      return array("no changing without a good reason !");

    $row = SQLLib::selectRow(sprintf_esc("select * from downloadlinks where prod = %d and id = %d",$_REQUEST["prod"],$input["linkID"]));
    if (!$row)
      return array("nice try :|");

    if (strcmp($row->link,$input["newLink"])===0 && strcasecmp($row->type,$input["newLinkKey"])===0)
      return array("you didn't change anything :|");

    $output["linkID"] = $input["linkID"];
    $output["oldLink"] = $input["oldLink"];
    $output["oldLinkKey"] = $input["oldLinkKey"];
    $output["newLink"] = $input["newLink"];
    $output["newLinkKey"] = $input["newLinkKey"];
    $output["reason"] = $input["reason"];
    return array();
  }

  static function Display($itemID, $data)
  {
    $row = SQLLib::selectRow(sprintf_esc("select * from downloadlinks where id = %d",$data["linkID"]));
    $s = "<b>current</b>: ";
    $s .= _html($row->type)." - ";
    $s .= "<a href='"._html($row->link)."'>"._html(shortify_cut($row->link,50))."</a>";

    if ($row->type != $data["oldLink"])
    {
      $s .= "<br/>";
      $s .= "<b>old</b>: ";
      $s .= _html($data["oldLinkKey"])." - ";
      $s .= "<a href='"._html($data["oldLink"])."'>"._html(shortify_cut($data["oldLink"],50))."</a>";
    }
    
    $s .= "<br/>";
    $s .= "<b>new</b>: ";
    $s .= _html($data["newLinkKey"])." - ";
    $s .= "<a href='"._html($data["newLink"])."'>"._html(shortify_cut($data["newLink"],50))."</a>";
    
    $s .= "<br/>";
    $s .= "<b>reason</b>: ";
    $s .= _html($data["reason"]);
    return $s;
  }

  static function Process($itemID, $reqData)
  {
    $a = array();
    $a["type"] = $reqData["newLinkKey"];
    $a["link"] = $reqData["newLink"];
    SQLLib::UpdateRow("downloadlinks",$a,"id=".(int)$reqData["linkID"]);
    return array();
  }
};


///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassRemoveLink extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "remove an existing extra link"; }

  static function GetFields($data,&$fields,&$js)
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
        "required"=>true,
      ),
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );
  }

  static function ValidateRequest($input,&$output)
  {
    if (!SQLLib::selectRow(sprintf_esc("select * from downloadlinks where prod = %d and id = %d",$_REQUEST["prod"],$input["linkID"])))
      return array("nice try :|");

    if (!$input["reason"])
      return array("no deleting without a good reason !");

    $output["linkID"] = $input["linkID"];
    $output["reason"] = $input["reason"];
    return array();
  }

  static function Display($itemID, $data)
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
    return array();
  }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassAddCredit extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "add a new credit to a prod"; }

  static function GetFields($data,&$fields,&$js)
  {
    $fields = array(
      "userID" => array(
        "name"=>"user",
      ),
      "userRole" => array(
        "name"=>"user's role",
        "info"=>"Please separate roles with commas, e.g. 'Code, graphics, music'",
      ),
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );

    $js  = "document.observe('dom:loaded',function(){";
    $js .= "  new Autocompleter($('userID'), {\n";
    $js .= "    'dataUrl':'./ajax_users.php',\n";
    $js .= "    'processRow': function(item) {\n";
    $js .= "      return \"<img class='avatar' src='".POUET_CONTENT_URL."avatars/\" + item.avatar.escapeHTML() + \"'/> \" + item.name.escapeHTML() + \" <span class='glops'>\"+item.glops+\" glöps</span>\";\n";
    $js .= "    }\n";
    $js .= "  });\n";
    $js .= "});\n";
  }

  static function ValidateRequest($input,&$output)
  {
    if (!SQLLib::selectRow(sprintf_esc("select * from users where id = %d",$input["userID"])))
      return array("nice try :|");

    if (SQLLib::selectRow(sprintf_esc("select * from credits where prodID = %d and userID = %d",$_REQUEST["prod"],$input["userID"])))
      return array("there's already a credit for this user !");

    if (!$input["userRole"])
      return array("roles are important !");

    $output["userID"] = $input["userID"];
    $output["userRole"] = $input["userRole"];
    $output["oldUserID"] = $input["oldUserID"];
    $output["oldUserRole"] = $input["oldUserRole"];
    return array();
  }

  static function Display($itemID, $data)
  {
    $user = PouetUser::Spawn($data["userID"]);
    $s = "";
    if ($user)
    {
      $s .= $user->PrintLinkedAvatar()." ";
      $s .= $user->PrintLinkedName();
    }
    $s .= " - "._html($data["userRole"]);
    return $s;
  }

  static function Process($itemID, $reqData)
  {
    $a = array();
    $a["prodID"] = $itemID;
    $a["userID"] = $reqData["userID"];
    $a["role"] = $reqData["userRole"];
    SQLLib::InsertRow("credits",$a);
    return array();
  }
};


///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassChangeCredit extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "change an existing credit"; }

  static function GetFields($data,&$fields,&$js)
  {
    if ($data["creditID"])
    {
      $l = SQLLib::SelectRow(sprintf_esc("select * from credits where id = %d",$data["creditID"]));
      $fields = array(
        "creditID" => array(
          "type"=>"hidden",
          "value"=>(int)$data["creditID"],
        ),
        "oldUserID" => array(
          "type"=>"hidden",
          "value"=>(int)$l->userID,
        ),
        "oldUserRole" => array(
          "type"=>"hidden",
          "value"=>$l->role,
        ),
        "userID" => array(
          "name"=>"user",
          "type"=>"text",
          "value"=>$l->userID,
        ),
        "userRole" => array(
          "name"=>"user's role",
          "value"=>$l->role,
          "info"=>"Please separate roles with commas, e.g. 'Code, graphics, music'",
        ),
        "finalStep" => array(
          "type"=>"hidden",
          "value"=>1,
        ),
      );

      $js  = "document.observe('dom:loaded',function(){";
      $js .= "  new Autocompleter($('userID'), {\n";
      $js .= "    'dataUrl':'./ajax_users.php',\n";
      $js .= "    'processRow': function(item) {\n";
      $js .= "      return \"<img class='avatar' src='".POUET_CONTENT_URL."avatars/\" + item.avatar.escapeHTML() + \"'/> \" + item.name.escapeHTML() + \" <span class='glops'>\"+item.glops+\" glöps</span>\";\n";
      $js .= "    }\n";
      $js .= "  });\n";
      $js .= "});\n";
    }
    else
    {
      $s = new BM_Query("credits");
      $s->AddField("credits.id");
      $s->AddField("credits.role");
      $s->attach(array("credits"=>"userID"),array("users as user"=>"id"));
      $s->AddWhere(sprintf_esc("prodID = %d",$data["prod"]));
      $l = $s->perform();
      foreach($l as $v)
        $links[$v->id] = sprintf("%s [%s]",$v->user->nickname,$v->role);
      if (!$links)
        return "this prod has no credits to change !";
      $fields = array(
        "creditID" => array(
          "name"=>"select credit you want to edit",
          "type"=>"select",
          "fields"=>$links,
          "assoc"=>true,
        ),
      );
    }
  }

  static function ValidateRequest($input,&$output)
  {
    $row = SQLLib::selectRow(sprintf_esc("select * from credits where prodID = %d and id = %d",$_REQUEST["prod"],$input["creditID"]));
    if (!$row)
      return array("nice try :|");

    if (strcasecmp($row->role,$input["userRole"])===0 && $row->userID == $input["userID"])
      return array("you didn't change anything :|");

    if (!SQLLib::selectRow(sprintf_esc("select * from users where id = %d",$input["userID"])))
      return array("nice try :|");

    if (!$input["userRole"])
      return array("roles are important !");

    $output["creditID"] = $input["creditID"];
    $output["userID"] = $input["userID"];
    $output["userRole"] = $input["userRole"];
    return array();
  }

  static function Display($itemID, $data)
  {
    $s = new BM_Query("credits");
    $s->AddField("credits.id");
    $s->AddField("credits.role");
    $s->attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("credits.id = %d",$data["creditID"]));
    $s->SetLimit(1);
    $l = $s->perform();
    $row = reset($l);

    //$l = SQLLib::SelectRows(sprintf_esc("select credits.id,users.nickname,credits.role from credits left join users on users.id = credits.id where prodID = %d",$data["prod"]));
    $s = "<b>current</b>: ";
    if ($row->user)
    {
      $s .= $row->user->PrintLinkedAvatar()." ";
      $s .= $row->user->PrintLinkedName();
    }
    $s .= " - "._html($row->role);

    $s = new BM_Query("credits");
    $s->AddField("credits.id");
    $s->AddField("credits.role");
    $s->attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("credits.id = %d",$data["oldUserID"]));
    $s->SetLimit(1);
    $l = $s->perform();
    $row = reset($l);

    $s = "<br/><b>old</b>: ";
    if ($row->user)
    {
      $s .= $row->user->PrintLinkedAvatar()." ";
      $s .= $row->user->PrintLinkedName();
    }
    $s .= " - "._html($row->role);

    $user = PouetUser::Spawn($data["userID"]);
    $s .= "<br/><b>new</b>: ";
    if ($user)
    {
      $s .= $user->PrintLinkedAvatar()." ";
      $s .= $user->PrintLinkedName();
    }
    $s .= " - "._html($data["userRole"]);
    return $s;
  }

  static function Process($itemID, $reqData)
  {
    $a = array();
    $a["userID"] = $reqData["userID"];
    $a["role"] = $reqData["userRole"];
    SQLLib::UpdateRow("credits",$a,"id=".(int)$reqData["creditID"]);
    return array();
  }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassRemoveCredit extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "remove an existing credit"; }

  static function GetFields($data,&$fields,&$js)
  {
    $s = new BM_Query("credits");
    $s->AddField("credits.id");
    $s->AddField("credits.role");
    $s->attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("prodID = %d",$data["prod"]));
    $l = $s->perform();
    foreach($l as $v)
      $links[$v->id] = sprintf("%s [%s]",$v->user->nickname,$v->role);

    if (!$links)
      return "this prod has no credits to remove !";

    $fields = array(
      "creditID" => array(
        "name"=>"select credit you want removed",
        "type"=>"select",
        "fields"=>$links,
        "assoc"=>true,
      ),
      "reason" => array(
        "name"=>"why should this credit be deleted",
        "type"=>"textarea",
        "info"=>"moderator's note: abuse of this feature will result in retaliation. have a nice day.",
        "required"=>true,
      ),
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );
  }

  static function ValidateRequest($input,&$output)
  {
    if (!SQLLib::selectRow(sprintf_esc("select * from credits where prodID = %d and id = %d",$_REQUEST["prod"],$input["creditID"])))
      return array("nice try :|");

    if (!$input["reason"])
      return array("no deleting without a good reason !");

    $output["creditID"] = $input["creditID"];
    $output["reason"] = $input["reason"];
    return array();
  }

  static function Display($itemID, $data)
  {
    $s = new BM_Query("credits");
    $s->AddField("credits.id");
    $s->AddField("credits.role");
    $s->attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("credits.id = %d",$data["creditID"]));
    $s->SetLimit(1);
    $l = $s->perform();
    $row = reset($l);

    //$l = SQLLib::SelectRows(sprintf_esc("select credits.id,users.nickname,credits.role from credits left join users on users.id = credits.id where prodID = %d",$data["prod"]));
    $s = "<b>old</b>: ";
    if ($row->user)
    {
      $s .= $row->user->PrintLinkedAvatar()." ";
      $s .= $row->user->PrintLinkedName();
    }
    $s .= " - "._html($row->role);

    return $s;
  }

  static function Process($itemID, $reqData)
  {
    SQLLib::Query(sprintf_esc("delete from credits where id=%d",$reqData["creditID"]));
    return array();
  }
};


///////////////////////////////////////////////////////////////////////////////

class PouetRequestClassChangeDownloadLink extends PouetRequestClassBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "change download link"; }

  static function GetFields($data,&$fields,&$js)
  {
    $prod = PouetProd::Spawn( $data["prod"] );
    $fields = array(
      "oldDownloadLink" => array(
        "type"=>"hidden",
        "value"=>$prod->download,
      ),
      "downloadLink" => array(
        "name"=>"enter new download link",
        "type"=>"url",
        "value"=>$prod->download,
      ),
      "reason" => array(
        "name"=>"why should this link be changed",
        "type"=>"textarea",
        "info"=>"moderator's note: abuse of this feature will result in retaliation. have a nice day.",
        "required"=>true,
      ),
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );
  }

  static function ValidateRequest($input,&$output)
  {
    $errors = validateDownloadLink($input["downloadLink"]);
    if ($errors)
      return $errors;

    if (!$input["reason"])
      return array("no changing without a good reason !");

    $prod = PouetProd::Spawn( $_REQUEST["prod"] );
    if (!$prod)
      return array("nice try :|");

    if (strcmp($prod->download,$input["downloadLink"])===0)
      return array("you didn't change anything :|");

    $output["oldDownloadLink"] = $input["oldDownloadLink"];
    $output["downloadLink"] = $input["downloadLink"];
    $output["reason"] = $input["reason"];
    return array();
  }

  static function Display($itemID, $data)
  {
    $prod = PouetProd::Spawn( $itemID );
    $s = "<b>current</b>: ";
    $s .= "<a href='"._html($prod->download)."'>"._html(shortify_cut($prod->download,50))."</a>";
    if ($prod->download != $data["oldDownloadLink"])
    {
      $s .= "<br/><b>old</b>: ";
      $s .= "<a href='"._html($data["oldDownloadLink"])."'>"._html(shortify_cut($data["oldDownloadLink"],50))."</a>";
    }
    $s .= "<br/><b>new</b>: ";
    $s .= "<a href='"._html($data["downloadLink"])."'>"._html(shortify_cut($data["downloadLink"],50))."</a>";
    $s .= "<br/><b>reason</b>: ";
    $s .= _html($data["reason"]);
   
    return $s;
  }

  static function Process($itemID, $reqData)
  {
    $a = array();
    $a["download"] = $reqData["downloadLink"];
    SQLLib::UpdateRow("prods",$a,"id=".(int)$itemID);
    
    SQLLib::Query(sprintf_esc("delete from prods_linkcheck where prodID = %d",$itemID));
    
    return array();
  }
};

$REQUESTTYPES = array(
  "prod_add_link" => "PouetRequestClassAddLink",
  "prod_change_link" => "PouetRequestClassChangeLink",
  "prod_remove_link" => "PouetRequestClassRemoveLink",

  "prod_add_credit" => "PouetRequestClassAddCredit",
  "prod_change_credit" => "PouetRequestClassChangeCredit",
  "prod_remove_credit" => "PouetRequestClassRemoveCredit",

  "prod_change_downloadlink" => "PouetRequestClassChangeDownloadLink",
);
?>
