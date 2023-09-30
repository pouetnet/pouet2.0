<?php
global $PLATFORMS;
$PLATFORMS = handle_db_cache( POUET_ROOT_LOCAL . "/cache/enum-platforms.cache", function() {
  $rows = SQLLIB::selectRows("select * from platforms");
  $platforms = array();
  foreach($rows as $r)
  {
    $platforms[ $r->id ] = get_object_vars($r);
    unset($platforms[ $r->id ]["id"]);
    $platforms[ $r->id ]["slug"] = strtolower(preg_replace("/[^a-zA-Z0-9]+/","",$platforms[ $r->id ]["name"]));
  }
  ksort($platforms);
  return $platforms;
});

global $COMPOTYPES;
$COMPOTYPES = handle_db_cache( POUET_ROOT_LOCAL . "/cache/enum-compotypes.cache", function() {
  $rows = SQLLib::selectRows("select * from compotypes");
  
  $compos = array();
  foreach($rows as $v) $compos[$v->id] = $v->componame;
  ksort($compos);
  return $compos;
});

global $AWARDSSUGGESTIONS_CATEGORIES;
$AWARDSSUGGESTIONS_CATEGORIES = handle_db_cache( POUET_ROOT_LOCAL . "/cache/awardssuggestions-categories.cache", function() {
  $rows = SQLLib::selectRows("select * from awardssuggestions_categories");
  
  $cats = array();
  foreach($rows as $v) $cats[$v->id] = $v;
  ksort($cats);
  return $cats;
});

global $AWARDSSUGGESTIONS_EVENTS;
$AWARDSSUGGESTIONS_EVENTS = handle_db_cache( POUET_ROOT_LOCAL . "/cache/awardssuggestions-events.cache", function() {
  $rows = SQLLib::selectRows("select * from awardssuggestions_events");
  
  $cats = array();
  foreach($rows as $v) $cats[$v->id] = $v;
  ksort($cats);
  return $cats;
});

global $AWARDS_CATEGORIES;
$AWARDS_CATEGORIES = handle_db_cache( POUET_ROOT_LOCAL . "/cache/awards-categories.cache", function() {
  $rows = SQLLib::selectRows("select * from awards_categories");
  
  $cats = array();
  foreach($rows as $v) $cats[$v->id] = $v;
  ksort($cats);
  return $cats;
});

$AFFILIATIONS_ORIGINAL = array(
  "remix" => "remixed in",
  "port" => "ported to",
  "final" => "final version",
  "pack" => "packed in",
  "related" => "related to",
  "sequel" => "continued in",
);
$AFFILIATIONS_INVERSE = array(
  "remix" => "remix of",
  "port" => "ported from",
  "final" => "party version",
  "pack" => "includes",
  "related" => "related to",
  "sequel" => "sequel to",
);

$verificationStrings = array(
  "CELEBRANDIL-VECTOR",
  "MEKKA-SYMPOSIUM",
  "MEDIA-ERROR",
);

$IM_TYPES = array(
  "Discord"=>array(
    "capture"=>"^(.{3,32}#[0-9]{4}|[a-z0-9_\.]{2,32})$", // should support both "yo#1234" and ".yo"
  ),
  "Email"=>array(
    "capture"=>"^(\S*@\S*\.\S*)$",
    "display"=>function($in) { return sprintf("<a href='mailto:%s'>%s</a>",_html($in),_html($in)); }
  ),
  "Facebook"=>array(
    "capture"=>"(?:facebook\.com\/)?(\w+)\/?$",
    "display"=>function($in) { return sprintf("<a href='https://facebook.com/%s'>fb.com/%s</a>",_html($in),_html($in)); }
  ),
  "Instagram"=>array(
    "capture"=>"(?:instagram\.com\/)?@?(\w{1,15})\/?$",
    "display"=>function($in) { return sprintf("<a href='https://instagram.com/%s'>@%s</a>",_html($in),_html($in)); }
  ),
  "Jabber"=>array(
    "capture"=>"^(\S*@\S*\.\S*)$",
  ),
  "Mastodon"=>array(
    "capture"=>"^@?(\S*@\S*\.\S*)$",
    "display"=>function($in) { 
      list($user,$domain) = explode("@",$in);
      return sprintf("<a href='https://%s/@%s'>@%s@%s</a>",$domain,$user,_html($user),_html($domain));
    }
  ),
  "Skype"=>array(
    "capture"=>"^(?:(?:live\:)?([a-zA-Z][a-zA-Z0-9\.,\-_]{5,31}|\S*@\S*\.\S*))$", // ohoho HAVE FUN DEBUGGING THIS LOL
    "display"=>function($in) { return sprintf("<a href='skype:%s'>%s</a>",_html($in),_html($in)); }
  ),
  "Telegram"=>array(
    "capture"=>"(?:t\.me\/)?@?(\w{1,15})$",
    "display"=>function($in) { return sprintf("<a href='https://t.me/%s'>@%s</a>",_html($in),_html($in)); }
  ),
  "Twitch"=>array(
    "capture"=>"(?:twitch\.tv\/)?@?(\w{4,25})\/?$",
    "display"=>function($in) { return sprintf("<a href='https://twitch.tv/%s'>@%s</a>",_html($in),_html($in)); }
  ),
  "Twitter"=>array(
    "capture"=>"(?:twitter\.com\/)?@?(\w{1,15})\/?$",
    "display"=>function($in) { return sprintf("<a href='https://twitter.com/%s'>@%s</a>",_html($in),_html($in)); }
  ),
  "Bluesky"=>array(
    "capture"=>"(?:https:\/\/)?(?:bsky\.app\/profile\/)?([\w\.\-\_]+)$",
    "display"=>function($in) { return sprintf("<a href='https://bsky.app/profile/%s'>@%s</a>",_html($in),_html($in)); }
  ),
);

?>
