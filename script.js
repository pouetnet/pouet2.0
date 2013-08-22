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
    textarea.update( origTextarea.value );
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
      if (item.text.toLowerCase().indexOf("zx") != -1)
        $("row_zxdemoID").show();
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
}

function PrepareSubmitForm()
{
  NameWarning({"ajaxURL":"./ajax_prods.php","linkURL":"prod.php?which="});  

  var fields = $A([
    "row_csdbID",
    "row_zxdemoID",
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
  
  if ($("platform")) $("platform").observe("change",adjustSubmitFormFields);
  if ($("type")) $("type").observe("change",adjustSubmitFormFields);
  adjustSubmitFormFields();
  if ($("group1")) new Autocompleter($("group1"), {"dataUrl":"./ajax_groups.php"});
  if ($("group2")) new Autocompleter($("group2"), {"dataUrl":"./ajax_groups.php"});
  if ($("group3")) new Autocompleter($("group3"), {"dataUrl":"./ajax_groups.php"});
  if ($("partyID")) new Autocompleter($("partyID"), {"dataUrl":"./ajax_parties.php",onSelectItem:function(){
    $("row_partyYear").show();
    $("row_partyCompo").show();
    $("row_partyRank").show();
  }});
  if ($("invitationParty")) new Autocompleter($("invitationParty"), {"dataUrl":"./ajax_parties.php",onSelectItem:function(){
    $("row_invitationYear").show();
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
      var re1 = new RegExp("\\["+item,"g");
      var re2 = new RegExp("\\[/"+item,"g");
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
function fadeSuccess()
{
  var step = 1.0 / (timeCrossfade * 1.0 / timerDensity);
  $("successOverlay").style.opacity = parseFloat($("successOverlay").style.opacity) - step;
  if ($("successOverlay").style.opacity <= step + 0.01) // wahey chrome precision!
  {
    $("successOverlay").remove();
    return;
  }
  setTimeout(fadeSuccess,timerDensity);
}

function fireSuccessOverlay()
{
  if (!$("successOverlay"))
    document.body.insert(new Element("div",{"id":"successOverlay"}).update("Success !"));
  $("successOverlay").style.opacity = "1.0";
  setTimeout(fadeSuccess,timeWait + timerDensity);
}

document.observe("dom:loaded",function(){
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
});

function Youtubify( e )
{
  e.select("a").each(function(item){
    var videoID = item.href.match(/youtu(\.be\/|.*v=)([a-zA-Z0-9_\-]{11})/);
    if (videoID)
    {
      var callback = "ytcb";
      new Ajax.JSONRequest("https://gdata.youtube.com/feeds/api/videos/"+videoID[2]+"?v=2&alt=json",{
        method: "get",
        onSuccess: function(transport) {
          if (transport.responseJSON.entry.title)
          {
            var s = transport.responseJSON.entry.title.$t;
            item.update( s.escapeHTML() );
          }
        },
      });
    }
  });
}

