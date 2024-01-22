function NameWarning(opt)
{
  if(!$("row_name")) return;
  
  var nameWarning = new Element("div",{"id":"nameWarning","class":"submitWarning"});
  $("row_name").parentNode.insertBefore(nameWarning,$("row_name").nextSibling);
  nameWarning.hide();
  
  var timeout = null;
  $("name").observe("keyup",function(){
    if ($("name").value.length > 0)
    {
      if (timeout) clearTimeout(timeout);
      timeout = setTimeout(function(){
        new Ajax.Request(opt.ajaxURL,{
          "method":"post",
          "parameters":$H({"search":$("name").value}).toQueryString(),
          "onSuccess":function(transport){
            if (transport.responseJSON.length > 0)
            {
              var result = transport.responseJSON[0];
              nameWarning.show();
              nameWarning.update("Did you mean <a href='"+opt.linkURL+result.id+"'>"+result.name.escapeHTML()+"</a>?");
            }
            else
              nameWarning.hide();
          },
        });
        timeout = null;
      }, 1000);
    }
  });
}

function AddPreviewButton( button )
{
  var origTextarea = document.body.select("textarea").first();
  if (!origTextarea) return;
  
  var previewButton = new Element("input",{"type":"submit","id":"submitPreview","value":"Preview"});
  button.parentNode.insertBefore(previewButton,button);
  previewButton.observe("click",function(e){
    e.stop();

    var w = 600;
    var h = 400;
    var x = (screen.width - w) / 2;
    var y = (screen.height - h) / 2;
    var wnd = window.open("","previewWnd","left="+x+",top="+y+",width="+w+",height="+h+",resizable=yes,status=yes,toolbar=no,location=no,menubar=no,scrollbars=yes");
  
    var form = new Element("form",{"method":"post","target":"previewWnd","action":"preview.php"});
    document.body.insert(form);
    
    var textarea = new Element("textarea",{"name":"message"});
    //textarea.update( origTextarea.value );
    textarea.value = origTextarea.value;
    form.insert(textarea);
    
    form.submit();

    form.remove();
  });
}

function adjustSubmitFormFields()
{
  if($("platform")) $A( $("platform").options ).each(function(item){
    if (item.selected)
    {
//      if (item.text.toLowerCase().indexOf("zx") != -1)
//        $("row_zxdemoID").show();
      if (item.text.toLowerCase().indexOf("commodore") != -1)
        $("row_csdbID").show();
      if (item.text.toLowerCase().indexOf("c64") != -1)
        $("row_csdbID").show();
      if (item.text.toLowerCase().indexOf("c16") != -1)
        $("row_csdbID").show();
    }
  });
  if($("type")) $A( $("type").options ).each(function(item){
    if (item.selected)
    {
      if (item.text.toLowerCase().indexOf("invitation") != -1)
      {
        $("row_invitationParty").show();
        $("row_invitationYear").show();
      }
      if (item.text.toLowerCase().indexOf("bbstro") != -1)
      {
        $("row_boardID").show();
      }
    }
  });
  if ($("invitationParty") && $("invitationParty").value)
  {
    $("row_invitationParty").show();
    $("row_invitationYear").show();
  }
}

function PrepareSubmitForm()
{
  NameWarning({"ajaxURL":"./ajax_prods.php","linkURL":"prod.php?which="});  

  var fields = $A([
    "row_csdbID",
    //"row_zxdemoID",
    "row_invitationParty",
    "row_invitationYear",
    "row_boardID",
  ]);
  if ($("partyID"))
  {
    fields.concat([
      "row_partyYear",
      "row_partyCompo",
      "row_partyRank",
    ]);
  }
  fields.each(function(item){
    if ($(item)) $(item).hide();
  });
  
  var resetCompos = function()
  {
    var options = $("partyCompo").select("option");
    var v = $("partyCompo").options[$("partyCompo").selectedIndex].value;
    $("partyCompo").update("");
    options.each(function(i){ $("partyCompo").insert(i); });
    $("partyCompo").value = v;
  }
  var reorganizeCompos = function(){
    if (!$("partyID").value) { resetCompos(); return; }
    if (!$("partyYear").options[$("partyYear").selectedIndex].value) { resetCompos(); return; }
    new Ajax.Request("ajax_partycompos.php",{
      "method":"GET",
      "parameters":$H({
        "party":$("partyID").value,
        "year":$("partyYear").options[$("partyYear").selectedIndex].value
      }),
      "onException": function(r, e) { throw e; },
      "onSuccess":function(transport)
        {
          if (transport.responseJSON && transport.responseJSON.compos.length)
          {
            var options = $("partyCompo").select("option");
            var v = $("partyCompo").options[$("partyCompo").selectedIndex].value;
            $("partyCompo").update("");
            var idx = 0;
            var og1 = new Element("optgroup",{"label":"known compos at this party"});
            $("partyCompo").insert(og1);
            options.each(function(i){
              if (transport.responseJSON.compos.indexOf(parseInt(i.value,10)) != -1)
                og1.insert( i );
            });

            var og2 = new Element("optgroup",{"label":"other compos"});
            $("partyCompo").insert(og2);
            options.each(function(i){
              if (transport.responseJSON.compos.indexOf(parseInt(i.value,10)) == -1)
                og2.insert( i );
            });
            
            $("partyCompo").insert( {top:options.find(function(i){ return i.value == 0; })} );
            $("partyCompo").value = v;
            
          }
          else
          {
            resetCompos();
          }
        },
    });
  };
  
  if ($("platform")) $("platform").observe("change",adjustSubmitFormFields);
  if ($("type")) $("type").observe("change",adjustSubmitFormFields);
  adjustSubmitFormFields();
  if ($("group1")) new Autocompleter($("group1"), {"dataUrl":"./ajax_groups.php","processRow": function(item) {
    return item.name.escapeHTML() + (item.disambiguation ? " <span class='group-disambig'>" + item.disambiguation.escapeHTML() + "</span>" : "");
  }});
  if ($("group2")) new Autocompleter($("group2"), {"dataUrl":"./ajax_groups.php","processRow": function(item) {
    return item.name.escapeHTML() + (item.disambiguation ? " <span class='group-disambig'>" + item.disambiguation.escapeHTML() + "</span>" : "");
  }});
  if ($("group3")) new Autocompleter($("group3"), {"dataUrl":"./ajax_groups.php","processRow": function(item) {
    return item.name.escapeHTML() + (item.disambiguation ? " <span class='group-disambig'>" + item.disambiguation.escapeHTML() + "</span>" : "");
  }});
  if ($("partyID")) new Autocompleter($("partyID"), {"dataUrl":"./ajax_parties.php",onSelectItem:function(){
    $("row_partyYear").show();
    $("row_partyCompo").show();
    $("row_partyRank").show();
    reorganizeCompos();
  }});
  $("partyYear").observe("change",reorganizeCompos);
  $("partyYear").observe("keyup",reorganizeCompos);
  if ($("invitationParty")) new Autocompleter($("invitationParty"), {"dataUrl":"./ajax_parties.php",onSelectItem:function(){
    //$("row_invitationYear").show();
  }});
  if ($("boardID")) new Autocompleter($("boardID"), {"dataUrl":"./ajax_boards.php"});
}

function PreparePostForm( form )
{
  form.observe('submit',function(e){
    var ta = e.element().down("textarea");
    var bbTags = $A(["url", "code", "b", "i", "u", "img", "quote", "list"]);
    var broken = false;
    bbTags.each(function(item){
      var re1 = new RegExp("\\["+item+"[^a-z]","gi");
      var re2 = new RegExp("\\[/"+item+"[^a-z]","gi");
      if ((ta.value.match(re1) || []).length != (ta.value.match(re2) || []).length)
        broken = item;
    });    
    if (broken)
    {
      if (!confirm("your ["+broken+"] bbcode is broken ! are you sure you want to post it ?"))
        e.stop();
    }
  });
}
function InstrumentAdminEditorForAjax( parentElement, formAction, _options )
{
  var options = _options || {};
  
  parentElement.select(".edit").each(function(item){
    var tr = item.up("tr");
    item.observe("click",function(e){
      e.stop();
      new Ajax.Updater($(tr), item.href, {
        method: 'get',
        parameters: {
          partial: true,
        },
        onComplete: function() {
          if (options.onRowLoad)
            options.onRowLoad($(tr));
          tr.down("input[type='submit']").observe("click",function(ev){
            ev.stop();
            var invalidFields = tr.select("input,select").filter(function(item){ return item.validity && !item.validity.valid; });
            if (invalidFields.length > 0)
            {
              alert("There are invalid values in the following fields: " + invalidFields.collect(function(i){ return i.name}).join(", ") );
              return;
            }
            var opt = Form.serializeElements( tr.select("input,select"), {hash:true} );
            opt["partial"] = true;
            opt["formProcessorAction"] = tr.up("form").down("input[name='formProcessorAction']").value;
            new Ajax.Request( tr.up("form").action, {
              method: "post",
              parameters: opt,
              onSuccess: function(transport) {
                tr.update(transport.responseText);
                InstrumentAdminEditorForAjax( tr, formAction, options );
                fireSuccessOverlay();
              }
            });
          });
        },
      });
    });
  });
  parentElement.select(".delete").each(function(item){
    var tr = item.up("tr");
    item.observe("click",function(e){
      e.stop();
      if(!confirm("Are you sure you want to delete this item?"))
        return;
  
      //var opt = item.href.toQueryParams();
      var opt = Form.serializeElements( tr.select("input,select"), {hash:true} );
      opt = $H(opt).merge( item.href.toQueryParams() ).toObject();
      opt["partial"] = true;
      opt["formProcessorAction"] = tr.up("form").down("input[name='formProcessorAction']").value;
      new Ajax.Request( tr.up("form").action, {
        method: "post",
        parameters: opt,
        onSuccess: function(transport) {
          tr.remove();
          fireSuccessOverlay();
        }
      });
    });
  });
  if (parentElement.down(".new"))
  {
    parentElement.down(".new").observe("click",function(e){
      var tr = new Element("tr");
      parentElement.down("table.boxtable").insert(tr);
      e.stop();
      new Ajax.Updater($(tr), e.element().href, {
        method: 'get',
        parameters: {
          partial: true,
        },
        onComplete: function() {
          if (options.onRowLoad)
            options.onRowLoad($(tr));
          tr.down("input[type='submit']").observe("click",function(ev){
            ev.stop();
            var invalidFields = tr.select("input,select").filter(function(item){ return item.validity && !item.validity.valid; });
            if (invalidFields.length > 0)
            {
              alert("There are invalid values in the following fields: " + invalidFields.collect(function(i){ return i.name}).join(", ") );
              return;
            }
            var opt = Form.serializeElements( tr.select("input,select"), {hash:true} );
            opt["partial"] = true;
            opt["formProcessorAction"] = tr.up("form").down("input[name='formProcessorAction']").value;
            new Ajax.Request( tr.up("form").action, {
              method: "post",
              parameters: opt,
              onSuccess: function(transport) {
                tr.update(transport.responseText);
                InstrumentAdminEditorForAjax( tr, formAction, options );
                fireSuccessOverlay();
              }
            });
          });
        },
      });
    });
  }
}





var timerDensity = 50;
var timeCrossfade = 500;
var timeWait = 1000;
function fadeOverlays()
{
  var step = 1.0 / (timeCrossfade * 1.0 / timerDensity);
  var elements = ["successOverlay","errorOverlay"];
  var success = false;
  elements.each(function(item){
    if (!$(item)) return;
    item = $(item);
    
    item.style.opacity = parseFloat(item.style.opacity) - step;
    //console.log(item.style.opacity);
    if (item.style.opacity <= step + 0.01) // wahey chrome precision!
    {
      item.remove();
      return;
    }
    success = true;
  });
  if (!success) return;
  setTimeout(fadeOverlays,timerDensity);
}

function fireSuccessOverlay( msg )
{
  if (!$("successOverlay"))
    document.body.insert(new Element("div",{"id":"successOverlay"}).update( msg ? msg : "Success !"));
  $("successOverlay").style.opacity = "1.0";
  //console.log(timeWait + timerDensity);
  setTimeout(fadeOverlays,timeWait + timerDensity);
}

function fireErrorOverlay( errors )
{
  if (!$("errorOverlay"))
    document.body.insert(new Element("div",{"id":"errorOverlay"}).update( errors ? errors : "There was an error :(" ));
  $("errorOverlay").style.opacity = "1.0";
  setTimeout(fadeOverlays,timeWait + timerDensity);
}

function StubLinksToDomainName( parentElement, detailed )
{
  parentElement.select("a[rel='external']").each(function(item){
    var host = item.href.match(/:\/\/(.*?)\//);
    if (host)
    {
      item.update( host[1].escapeHTML() );
      return;
    }
  });
}

/* BBCODE helper script by AMcBain */

(function ()
{
  "use strict"; // See http://daringfireball.net/2010/07/improved_regex_for_matching_urls
  var textarea, buttons, url = /^\s*((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?\xAB\xBB\u201C\u201D\u2018\u2019]))/i;
  var protocol = /^\s*[A-Z_a-z]+:\/\//;

  // Replaces the selected text in a textarea with the given text optionally highlighting a subset
  // of the replacement text based on insets from the start and end of the replacement text.
  function replaceText (text, sinset, einset)
  {
    var start = textarea.selectionStart, end = textarea.selectionEnd;

    textarea.value = textarea.value.substring(0, start) + text + textarea.value.substring(end);

    if (typeof sinset === "number" && typeof einset === "number") {
      textarea.setSelectionRange(start + sinset, start + text.length - einset);
    }
    else
    {
      textarea.setSelectionRange(start + text.length, start + text.length);
    }
    textarea.focus();
  }

  function getText ()
  {
    return textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
  }

  // Puts proper bb-code around selected text and places the cursor after it,
  // or inserts some bb-code-surrounded dummy text and selects the dummy text.
  function simpleEdit ()
  {
    var text = getText(), start = this.code[0], end = this.code[1];

    if (text) {
      replaceText(start + text + end);
    }
    else {
      replaceText(start + this.text + end, start.length, end.length);
    }
  }

  function linkAltEdit ()
  {
    var text = getText(), start = "[url=", end = "[/url]";

    if (text)
    {
      if (url.test(text))
      {
        if (!protocol.test(text))
        {
          text = "http://" + text;
        }
        text = text.strip();
        replaceText(start + text + "]" + this.smpl + end, start.length + text.length + 1, end.length);
      }
      else
      {
        replaceText(start + this.text + "]" + text + end, start.length, end.length + text.length + 1);
      }
    }
    else
    {
      text = start + this.text + "]" + this.smpl + end;
      replaceText(text, start.length, 1 + this.smpl.length + end.length);
    }
  }

  function listEdit ()
  {
    var text = getText(), start = this.code[0], end = this.code[1];

    if (text)
    {
      text = start + text.replace(/(?:^|\n)([^\n]+)/g, "[*]$1\n") + end;
      replaceText(text, text.length + 1, 0);
    }
    else
    {
      text = start + this.text + end;
      replaceText(text, start.length + 3, end.length + 1);
    }
  }

  buttons = [ {
    name: "Bold",
    code: [ "[b]", "[/b]" ],
    text: "bold text"
  }, {
    name: "Italic",
    code: [ "[i]", "[/i]" ],
    text: "italic text"
  }, {
    name: "Link",
    code: [ "[url]", "[/url]" ],
    text: "http://example.com",
    click: function ()
    {
      var text = getText();

      if (url.test(text) && !protocol.test(text))
      {
        this.smpl = text.strip();
        linkAltEdit.call(this);
      }
      else
      {
        simpleEdit.call(this);
      }
    }
  }, {
    "class": "link_alt",
    name: "Link w/ text",
    text: "http://example.com",
    smpl: "linky",
    click: linkAltEdit
  }/*, {
    name: "Email",
    code: [ "[email]", "[/email]" ],
    text: "example@example.com"
  }*/, {
    name: "Image",
    code: [ "[img]", "[/img]" ],
    text: "http://example.com/image.png"
  }, {
    name: "Quote",
    code: [ "[quote]", "[/quote]" ],
    text: "quoted text"
  }, {
    name: "Code",
    code: [ "[code]", "[/code]" ],
    text: "// code comment"
  }, {
    name: "Bullet list",
    more: true,
    code: [ "[list]\n", "[/list]" ],
    text: "[*]list item\n",
    click: listEdit
  }, {
    name: "Alpha list",
    more: true,
    code: [ "[list=A]\n", "[/list]" ],
    text: "[*]A is for alligator\n",
    click: listEdit
  }, {
    name: "Numbered list",
    more: true,
    code: [ "[list=1]\n", "[/list]" ],
    text: "[*]list item\n",
    click: listEdit
  } ];

  document.observe("dom:loaded", function ()
  {
    textarea = $$("#pouetbox_bbsopen textarea, #pouetbox_bbspost textarea, #pouetbox_prodpost textarea")[0];

    // Require IE9+ and other supporting browsers.
    if(!textarea || !("selectionStart" in textarea)) {
      return;
    }

    var list = document.createElement("ul");
    list.id = "pouet_bb_editor";

    var sublist = document.createElement("ul");

    buttons.each(function (button)
    {
      var li = document.createElement("li");
      li.innerHTML = button.name;
      li.onclick = function ()
      {
        (button.click || simpleEdit).call(button);
      };

      var parent = (button.more ? sublist : list);
      parent.appendChild(li);
      parent.appendChild(document.createTextNode(" "));
    });

    list.insertAdjacentHTML("beforeend", "<li>more...</li>");
    list.lastChild.appendChild(sublist);

    textarea.nextSibling.nextSibling.appendChild(list);
  });
}());

function CollapsibleHeaders( elements )
{
  elements.each(function(box){
    if (!box.down("h2,h3"))
      return;
    
    var header = box.down("h2,h3");
    var elements = header.nextSiblings();
    
    var container = new Element("div",{"class":"collapseContainer"});
    
    elements.each(function(i){ container.insert(i); });
    
    box.insert(container);
    
    var toggle = new Element("span",{"class":"collapseToggle"});
    header.insert( toggle );

    if (box.id && Cookie.getData('pouetHeadersShown-'+box.id))
    {
      toggle.update("hide");
      container.show();
    }
    else
    {
      toggle.update("show");
      container.hide();
    }    
    
    var _box = box;
    header.observe("click",function(){
      if (toggle.innerHTML == "show")
      {
        toggle.update("hide");
        container.show();
        if (_box && _box.id) Cookie.setData('pouetHeadersShown-'+_box.id,true);
      }
      else
      {
        toggle.update("show");
        container.hide();
        if (_box && _box.id) Cookie.setData('pouetHeadersShown-'+_box.id,false);
      }
    });      
  });
}

function checkForNewsTickers()
{
  if (!newsTickers)
  {
    return;
  }
  
  var container = null;
  $H(newsTickers).each(function(kvp){
    if(Date.now() > kvp.value.expires*1000)
    {
      return;
    }
    if (Cookie.getData("newsTickerSupress-"+kvp.key))
    {
      return;
    }
    if (!container)
    {
      container = new Element("ul",{"class":"newsTickers"});
      document.body.insert(container);
    }
    var li = new Element("li",{"class":kvp.value.class}).update("<p>"+kvp.value.html+"</p>");
    var close = new Element("button",{"class":"close","title":"Close"}).update("&#x274C;");
    close.observe("click",function(){
      li.remove();
      Cookie.setData("newsTickerSupress-"+kvp.key, true);
    });
    li.insert(close);
    container.insert(li);
  });
}

// on load scripts - keep this to minimum
document.observe("dom:loaded",function(){
  Cookie.init({name: 'pouetSettings', expires: 365});
  if (location.hash=="#success")
  {
    if (history && "pushState" in history)
    {
      history.pushState("", document.title, window.location.pathname + window.location.search);
    }
    else
    {
      location.hash = "";
    }
    fireSuccessOverlay();
  }
  checkForNewsTickers();
});
