<?php
///////////////////////////////////////////////////////////////////////////////

class PouetRequest_Prod_AddLink extends PouetRequestBase
{
  static $links = array(
    "/https:\/\/(?:www\.)?demozoo\.org\/productions\/(\d+)/" => "demozoo",
    "/https:\/\/(?:www\.)?csdb\.dk\/release\/\?id=(\d+)/" => "csdb",
  );

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
        "maxlength"=>256,
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
    $s .= "<a href='"._html($data["newLink"])."' rel='external'>"._html(shortify_cut($data["newLink"],50))."</a>";
    foreach(self::$links as $k=>$v)
    {
      if (preg_match($k,$data["newLink"]))
      {
        $s.="<br/><b>(will be stored as ".$v." ID instead of a link)</b>";
      }
    }
    return $s;
  }

  static function Process($itemID, $reqData)
  {
    foreach(self::$links as $k=>$v)
    {
      if (preg_match($k,$reqData["newLink"],$m))
      {
        $a = array();
        $a[$v] = $m[1];
        SQLLib::UpdateRow("prods",$a,sprintf_esc("id=%d",$itemID));
        return array();
      }
    }
    $a = array();
    $a["prod"] = $itemID;
    $a["type"] = $reqData["newLinkKey"];
    $a["link"] = $reqData["newLink"];
    SQLLib::InsertRow("downloadlinks",$a);
    return array();
  }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequest_Prod_ChangeLink extends PouetRequestBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "change an existing extra link"; }

  static function GetFields($data,&$fields,&$js)
  {
    if (@$data["linkID"])
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
          "maxlength"=>256,
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
      $links = array();
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

    $s = "";
    
    if ($row)
    {
      $s .= "<b>current</b>: ";
      $s .= _html($row->type)." - ";
      $s .= "<a href='"._html($row->link)."' rel='external'>"._html(shortify_cut($row->link,50))."</a>";
    }

    if (@$data["oldLinkKey"] && (!$row || $row->link != $data["oldLink"]))
    {
      $s .= "<br/>";
      $s .= "<b>old</b>: ";
      $s .= _html($data["oldLinkKey"])." - ";
      $s .= "<a href='"._html($data["oldLink"])."' rel='external'>"._html(shortify_cut($data["oldLink"],50))."</a>";
    }

    $s .= "<br/>";
    $s .= "<b>new</b>: ";
    $s .= _html($data["newLinkKey"])." - ";
    $s .= "<a href='"._html($data["newLink"])."' rel='external'>"._html(shortify_cut($data["newLink"],50))."</a>";

    if (@$data["reason"])
    {
      $s .= "<br/>";
      $s .= "<b>reason</b>: ";
      $s .= _html($data["reason"]);
    }
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

class PouetRequest_Prod_RemoveLink extends PouetRequestBase
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
    $s = "";
    if ($row)
    {
      $s .= _html($row->type)." - ";
      $s .= "<a href='"._html($row->link)."' rel='external'>"._html(shortify_cut($row->link,50))."</a>";
    }
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

class PouetRequest_Prod_AddCredit extends PouetRequestBase
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
    $output["oldUserID"] = @$input["oldUserID"];
    $output["oldUserRole"] = @$input["oldUserRole"];
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
    $a["role"] = trim($reqData["userRole"]);
    SQLLib::InsertRow("credits",$a);
    return array();
  }
};


///////////////////////////////////////////////////////////////////////////////

class PouetRequest_Prod_ChangeCredit extends PouetRequestBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "change an existing credit"; }

  static function GetFields($data,&$fields,&$js)
  {
    if (@$data["creditID"])
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
      $s = new BM_Query();
      $s->AddTable("credits");
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

    if (strcmp($row->role,$input["userRole"])===0 && $row->userID == $input["userID"])
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
    $s = new BM_Query();
    $s->AddTable("credits");
    $s->AddField("credits.id");
    $s->AddField("credits.role");
    $s->attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("credits.id = %d",$data["creditID"]));
    $s->SetLimit(1);
    $l = $s->perform();
    $row = reset($l);

    //$l = SQLLib::SelectRows(sprintf_esc("select credits.id,users.nickname,credits.role from credits left join users on users.id = credits.id where prodID = %d",$data["prod"]));
    $out = "<b>current</b>: ";
    if ($row && $row->user)
    {
      $out .= $row->user->PrintLinkedAvatar()." ";
      $out .= $row->user->PrintLinkedName();
    }
    $out .= " - "._html($row ? $row->role : "");

    $s = new BM_Query();
    $s->AddTable("credits");
    $s->AddField("credits.id");
    $s->AddField("credits.role");
    $s->attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("credits.id = %d",@$data["oldUserID"]?:0));
    $s->SetLimit(1);
    $l = $s->perform();
    $row = reset($l);

    $user = PouetUser::Spawn($data["userID"]);
    $out .= "<br/><b>new</b>: ";
    if ($user)
    {
      $out .= $user->PrintLinkedAvatar()." ";
      $out .= $user->PrintLinkedName();
    }
    $out .= " - "._html($data["userRole"]);
    return $out;
  }

  static function Process($itemID, $reqData)
  {
    $a = array();
    $a["userID"] = $reqData["userID"];
    $a["role"] = trim($reqData["userRole"]);
    SQLLib::UpdateRow("credits",$a,"id=".(int)$reqData["creditID"]);
    return array();
  }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequest_Prod_RemoveCredit extends PouetRequestBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "remove an existing credit"; }

  static function GetFields($data,&$fields,&$js)
  {
    $s = new BM_Query();
    $s->AddTable("credits");
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
    $s = new BM_Query();
    $s->AddTable("credits");
    $s->AddField("credits.id");
    $s->AddField("credits.role");
    $s->attach(array("credits"=>"userID"),array("users as user"=>"id"));
    $s->AddWhere(sprintf_esc("credits.id = %d",$data["creditID"]));
    $s->SetLimit(1);
    $l = $s->perform();
    $row = reset($l);

    //$l = SQLLib::SelectRows(sprintf_esc("select credits.id,users.nickname,credits.role from credits left join users on users.id = credits.id where prodID = %d",$data["prod"]));
    $s = "<b>old</b>: ";
    if (@$row->user)
    {
      $s .= $row->user->PrintLinkedAvatar()." ";
      $s .= $row->user->PrintLinkedName();
    }
    $s .= " - "._html(@$row->role);

    return $s;
  }

  static function Process($itemID, $reqData)
  {
    SQLLib::Query(sprintf_esc("delete from credits where id=%d",$reqData["creditID"]));
    return array();
  }
};

///////////////////////////////////////////////////////////////////////////////

class PouetRequest_Prod_ChangeDownloadLink extends PouetRequestBase
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
        "maxlength"=>256,
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
    if ($prod)
    {
      $s = "<b>current</b>: ";
      $s .= "<a href='"._html($prod->download)."' rel='external'>"._html(shortify_cut($prod->download,50))."</a>";
    }
    if (@$data["oldDownloadLink"] && $prod->download != $data["oldDownloadLink"])
    {
      $s .= "<br/><b>old</b>: ";
      $s .= "<a href='"._html($data["oldDownloadLink"])."' rel='external'>"._html(shortify_cut($data["oldDownloadLink"],50))."</a>";
    }
    $s .= "<br/><b>new</b>: ";
    $s .= "<a href='"._html($data["downloadLink"])."' rel='external'>"._html(shortify_cut($data["downloadLink"],50))."</a>";
    if (@$data["reason"])
    {
      $s .= "<br/><b>reason</b>: ";
      $s .= _html($data["reason"]);
    }

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

///////////////////////////////////////////////////////////////////////////////

class PouetRequest_Prod_ChangeInfo extends PouetRequestBase
{
  static function GetItemType() { return "prod"; }
  static function Describe() { return "change prod info"; }

  static function ProdToArray( $prod )
  {
    $fields["name"] = $prod->name;

    $n = 1;
    foreach($prod->groups as $g)
      $fields["group".$n++] = $g->id;

    $fields["releaseDate"] = $prod->releaseDate;

    $fields["platform"] = array_keys($prod->platforms);
    $fields["type"] = $prod->types;

    if (count($prod->placings) > 0)
    {
      $fields["partyID"] = $prod->placings[0]->party->id;
      $fields["partyYear"] = $prod->placings[0]->year;
      $fields["partyCompo"] = $prod->placings[0]->compo;
      $fields["partyRank"] = $prod->placings[0]->ranking;
    }

    //$fields["sceneOrgID"] = $prod->sceneorg;
    $fields["demozooID"] = $prod->demozoo;
    $fields["csdbID"] = $prod->csdb;
    $fields["boardID"] = $prod->boardID;
    $fields["invitationParty"] = $prod->invitation;
    $fields["invitationYear"] = $prod->invitationyear;

    return $fields;
  }
  static function GetFields($data,&$fields,&$js)
  {
    $row = SQLLib::selectRow("DESC prods type");
    $types = enum2array($row->Type);

    global $COMPOTYPES;
    $compos = $COMPOTYPES;
    $compos[0] = "";
    asort($compos);

    $ranks = array(0=>"");
    $ranks[97] = "disqualified";
    $ranks[98] = "not applicable";
    $ranks[99] = "not shown";
    for ($x=1; $x<=96; $x++) $ranks[$x] = $x;

    $years = array("");
    for ($x=date("Y"); $x>=POUET_EARLIEST_YEAR; $x--) $years[$x] = $x;
    $yearsFuture = array("");
    for ($x=date("Y")+2; $x>=POUET_EARLIEST_YEAR; $x--) $yearsFuture[$x] = $x;

    global $PLATFORMS;
    $plat = array();
    foreach($PLATFORMS as $k=>$v) $plat[$k] = $v["name"];
    uasort($plat,"strcasecmp");

    $fields = array(
      "name"=>array(
        "name"=>"prod name / title",
        "info"=>" ",
        "required"=>true,
      ),
      "group1"=>array(
        "name"=>"group 1",
      ),
      "group2"=>array(
        "name"=>"group 2",
      ),
      "group3"=>array(
        "name"=>"group 3",
        "infoAfter"=>"if the group is missing from the list, add it <a href='submit_group.php' target='_blank'>here</a> !",
      ),
      "releaseDate"=>array(
        "name"=>"release date",
        "type"=>"dateMonth",
      ),
      "type"=>array(
        "name"=>"type",
        "type"=>"select",
        "multiple"=>true,
        "fields"=>$types,
        "info"=>"ctrl + click or cmd + click to select more than one !",
        "required"=>true,
      ),
      "platform"=>array(
        "name"=>"platform",
        "type"=>"select",
        "multiple"=>true,
        "assoc"=>true,
        "fields"=>$plat,
        "info"=>"ctrl + click or cmd + click to select more than one !",
        "required"=>true,
      ),
      "csdbID"=>array(
        "name"=>"csdb ID",
      ),
      /*
      "sceneOrgID"=>array(
        "name"=>"scene.org ID",
      ),
      */
      "demozooID"=>array(
        "name"=>"demozoo ID",
      ),
      "partyID"=>array(
        "name"=>"party",
        "infoAfter"=>"if the party is missing from the list, add it <a href='submit_party.php' target='_blank'>here</a> !",
      ),
      "partyYear"=>array(
        "name"=>"party year",
        "type"=>"select",
        "fields"=>$years,
      ),
      "partyCompo"=>array(
        "name"=>"party compo",
        "type"=>"select",
        "fields"=>$compos,
        "assoc"=>true,
      ),
      "partyRank"=>array(
        "name"=>"party rank",
        "type"=>"select",
        "assoc"=>true,
        "fields"=>$ranks,
      ),
      "invitationParty"=>array(
        "name"=>"invitation for party",
      ),
      "invitationYear"=>array(
        "name"=>"invitation year",
        "type"=>"select",
        "fields"=>$yearsFuture,
      ),
      "boardID"=>array(
        "name"=>"bbs affiliation",
      ),
      "finalStep" => array(
        "type"=>"hidden",
        "value"=>1,
      ),
    );
    if (@$data["prod"] && $prod = PouetProd::Spawn( $data["prod"] ))
    {
      $a = array(&$prod);
      PouetCollectPlatforms( $a );

      $pa = static::ProdToArray( $prod );
      foreach($pa as $k=>$v)
        $fields[$k]["value"] = $v;
    }
    $js .= "document.observe(\"dom:loaded\",function(){\n";
    $js .= "  if (!$(\"row_csdbID\")) return;\n";
    $js .= "  PrepareSubmitForm();\n";
    $js .= "});\n";
  }

  static function ValidateRequest($input,&$output)
  {
    if (!trim($input["name"]))
    {
      return array("prod name can't be empty !");
    }

    $fields = array();
    $js = "";
    static::GetFields(array(),$fields,$js);
    $prod = PouetProd::Spawn( $_REQUEST["prod"] );
    $a = array(&$prod);
    PouetCollectPlatforms( $a );
    $pa = static::ProdToArray( $prod );

    $_in = array();
    foreach($fields as $k=>$v) $_in[$k] = @$input[$k];

    if( $input["releaseDate_year"] && $input["releaseDate_month"] && checkdate( (int)$input["releaseDate_month"], 15, (int)$input["releaseDate_year"]) )
      $_in["releaseDate"] = sprintf("%04d-%02d-15",$input["releaseDate_year"],$input["releaseDate_month"]);
    else if ($input["releaseDate_year"])
      $_in["releaseDate"] = sprintf("%04d-00-15",$input["releaseDate_year"]);

    if (!$_in["partyID"])
    {
      unset( $_in["partyYear"] );
      unset( $_in["partyCompo"] );
      unset( $_in["partyRank"] );
    }
    $output = array_diff_meaningful( $_in, $pa );

    if (array_diff( $_in["type"] ?: array(), $pa["type"] ))
    {
      $output["type"] = $_in["type"];
    }

    if (array_diff( $_in["platform"] ?: array(), $pa["platform"] ))
    {
      $output["platform"] = $_in["platform"];
    }

    unset($output["finalStep"]);
    
    return $output ? array() : array("you didn't change anything !");
  }

  static function Display($itemID, $data)
  {
    global $PLATFORMS;
    global $COMPOTYPES;

    $ranks = array(0=>"");
    $ranks[97] = "disqualified";
    $ranks[98] = "not applicable";
    $ranks[99] = "not shown";
    for ($x=1; $x<=96; $x++) $ranks[$x] = $x;

    $prod = PouetProd::Spawn( $itemID );
    if (!$prod)
    {
      return sprintf("prod %d missing.", $itemID);
    }
    $a = array(&$prod);
    PouetCollectPlatforms( $a );

    $fields = array();
    $js = "";
    static::GetFields(array(),$fields,$js);

    $s = "";
    foreach($data as $k=>$v)
    {
      $groupIdx = 0;
      switch($k)
      {
        case "type":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= $prod->RenderTypeIcons();
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $prod->types = $v ?: array();
          $s .= $prod->RenderTypeIcons();
          $s .= "<br/>";
          break;
        case "platform":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= $prod->RenderPlatformIcons();
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $prod->platforms = array();
          if (@$v) foreach($v as $a) $prod->platforms[] = $PLATFORMS[$a];
          $s .= $prod->RenderPlatformIcons();
          $s .= "<br/>";
          break;
        case "group3":
          $groupIdx++;
        case "group2":
          $groupIdx++;
        case "group1":
          $group = @$prod->groups[$groupIdx];
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= $group ? $group->RenderLong() : "<i>none</i>";
          $s .= "<br/>";
          $group = PouetGroup::Spawn( $v );
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= $group ? $group->RenderLong() : "<i>none</i>";
          $s .= "<br/>";
          break;
        case "partyID":
          $party = ($prod && $prod->party && $prod->party->id) ? PouetParty::Spawn( $prod->party->id ) : null;
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= $party ? $party->PrintLinked() : "<i>none</i>";
          $s .= "<br/>";
          $party = PouetParty::Spawn( $v );
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= $party ? $party->PrintLinked() : "<i>none</i>";
          $s .= "<br/>";
          break;
        case "invitationParty":
          $party = PouetParty::Spawn( $prod->invitation );
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= $party ? $party->PrintLinked() : "<i>none</i>";
          $s .= "<br/>";
          $party = PouetParty::Spawn( $v );
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= $party ? $party->PrintLinked() : "<i>none</i>";
          $s .= "<br/>";
          break;
        case "invitationYear":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= _html($prod->invitationyear);
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= _html($v);
          $s .= "<br/>";
          break;
        case "partyYear":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= @$prod->placings[0]->year;
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= _html($v);
          $s .= "<br/>";
          break;
        case "partyCompo":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= @$COMPOTYPES[$prod->placings[0]->compo];
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= @$COMPOTYPES[$v];
          $s .= "<br/>";
          break;
        case "partyRank":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= _html(@$ranks[$prod->placings[0]->ranking]);
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= _html(@$ranks[$v]);
          $s .= "<br/>";
          break;
        case "releaseDate":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= _html(renderHalfDate($prod->releaseDate));
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= _html(renderHalfDate($v));
          $s .= "<br/>";
          break;
        case "boardID":
          $board = PouetBoard::Spawn( $prod->{$k} );
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= $board ? $board->RenderLink() : "<i>none</i>";
          $s .= "<br/>";
          $board = PouetBoard::Spawn( $v );
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= $board ? $board->RenderLink() : "<i>none</i>";
          $s .= "<br/>";
          break;
        case "demozooID":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= $prod->demozoo ? sprintf("<a href='https://demozoo.org/productions/%d/'>%d</a>",$prod->demozoo,$prod->demozoo) : "<i>none</i>";
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= $v ? sprintf("<a href='https://demozoo.org/productions/%d/'>%d</a>",$v,$v) : "<i>none</i>";
          $s .= "<br/>";
          break;
        case "csdbID":
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= $prod->csdb ? sprintf("<a href='https://csdb.dk/release/?id=%d'>%d</a>",$prod->csdb,$prod->csdb) : "<i>none</i>";
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= $v ? sprintf("<a href='https://csdb.dk/release/?id=%d'>%d</a>",$v,$v) : "<i>none</i>";
          $s .= "<br/>";
          break;
        default:
          $s .= "<b>current ".$fields[$k]["name"]."</b>: ";
          $s .= _html($prod->{$k});
          $s .= "<br/>";
          $s .= "<b>new ".$fields[$k]["name"]."</b>: ";
          $s .= _html($v);
          $s .= "<br/>";
      }
    }
    return $s;
  }

  static function Process($itemID,$reqData)
  {
    $sql = array();
    foreach($reqData as $k=>$v)
    {
      switch($k)
      {
        case "partyID":    $sql["party"] = nullify($v); break;
        case "partyYear":  $sql["party_year"] = $v; break;
        case "partyCompo": $sql["party_compo"] = nullify($v); break;
        case "partyRank":  $sql["party_place"] = nullify($v); break;
        case "type":       $sql["type"] = implode(",",$v); break;
        case "platform":   break; // deal with this underneath
        case "demozooID":  $sql["demozoo"] = (int)$v; break;
        case "csdbID":     $sql["csdb"] = (int)$v; break;
        case "invitationParty": $sql["invitation"] = $v; break;
        case "group1":     $sql["group1"] = nullify($v); break;
        case "group2":     $sql["group2"] = nullify($v); break;
        case "group3":     $sql["group3"] = nullify($v); break;
        default:
          $sql[$k] = $v;
      }
    }
    if ($sql)
    {
      SQLLib::UpdateRow("prods",$sql,"id=".(int)$itemID);
    }

    if (isset($reqData["platform"]))
    {
      $data["platform"] = array_unique($reqData["platform"]);
      SQLLib::Query(sprintf_esc("delete from prods_platforms where prod = %d",(int)$itemID));
      foreach($data["platform"] as $v)
      {
        $a = array();
        $a["prod"] = (int)$itemID;
        $a["platform"] = $v;
        SQLLib::InsertRow("prods_platforms",$a);
      }
    }

  }
};

$REQUESTTYPES["prod_change_info"] = "PouetRequest_Prod_ChangeInfo";
$REQUESTTYPES["prod_change_downloadlink"] = "PouetRequest_Prod_ChangeDownloadLink";

$REQUESTTYPES["prod_add_link"] = "PouetRequest_Prod_AddLink";
$REQUESTTYPES["prod_change_link"] = "PouetRequest_Prod_ChangeLink";
$REQUESTTYPES["prod_remove_link"] = "PouetRequest_Prod_RemoveLink";

$REQUESTTYPES["prod_add_credit"] = "PouetRequest_Prod_AddCredit";
$REQUESTTYPES["prod_change_credit"] = "PouetRequest_Prod_ChangeCredit";
$REQUESTTYPES["prod_remove_credit"] = "PouetRequest_Prod_RemoveCredit";

?>
