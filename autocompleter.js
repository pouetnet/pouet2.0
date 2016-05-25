Autocompleter = Class.create({
  initialize: function(originalElement,options)
  {
    this.originalElement = originalElement;
    this.options = Object.extend({
      keyValue: true,
      dropdown: false,
      lockSelect: true,
      width: null,
      placeholder: "",
    },options || {}); 
    var instance = this;
    
    this.originalElement.hide();
    var parent = $(originalElement.parentNode);

    this.autoCompleteList = new Element("ul",{"class":"autocompleteList"});
    this.autoCompleteList.hide();
    parent.insert(this.autoCompleteList);

    if (this.options.lockSelect)
    {
      this.finalSelection = new Element("div",{"class":"autocompleteSelection","title":"click to edit!","style":"cursor:pointer"});
      this.finalSelection.hide();
      parent.insert(this.finalSelection);
    }

    this.searchBox = new Element("input",{"class":"autocompleteSearch","style":"position:relative","placeholder":this.options.placeholder});
    parent.insert( this.searchBox );

    if (this.options.dropdown)
    {
      this.dropButton = new Element("button",{"class":"autocompleteDropbutton"}).update("&#x25BC;");
      this.dropButton.observe("click",function(e){
        e.stop();
        $$(".autocompleteList").invoke("hide");
        instance.showAutocomplete("");
      });
      parent.insert( this.dropButton );
    }

    if (this.finalSelection)
    {
      this.finalSelection.observe("click",function(){
        instance.reset(false);
      });
    }
    this.keyboardSelection = -1;
    this.lastSearch = "";
    
    // we use this to hide the list if the user clicks on something else.
    // we have to use this because the "blur" event fires before the "click",
    // and we won't be able to use the click to find out what we clicked if
    // we hide the list.
    document.body.observe("click",function(e){
      if ( !instance.checkIfElementIsOurs( e ) )
      {
        instance.autoCompleteList.hide();
      }
    });
    this.searchBox.observe("focus",function(){
      if (instance.searchBox.value.length >= 1)
        instance.showAutocomplete( instance.searchBox.value );
    });
    
    this.searchBox.observe("keydown",function(e){
      var items = instance.autoCompleteList.select("li");
      if (e.keyCode == Event.KEY_UP)
      {
        e.stop();
        if (instance.keyboardSelection > 0)
        {
          instance.keyboardSelection--;
          items.invoke("removeClassName","selected");
          items[instance.keyboardSelection].addClassName("selected");
        }
        return;
      }
      if (e.keyCode == Event.KEY_DOWN)
      {
        e.stop();
        if (instance.keyboardSelection < items.length - 1)
        {
          instance.keyboardSelection++;
          items.invoke("removeClassName","selected");
          items[instance.keyboardSelection].addClassName("selected");
        }
        return;
      }
      if (e.keyCode == Event.KEY_RETURN)
      {
        e.stop();
        if (0 <= instance.keyboardSelection && instance.keyboardSelection < items.length)
        {
          instance.selectListItem( items[instance.keyboardSelection] );
        }
        return;
      }
      if (e.keyCode == Event.KEY_ESC)
      {
        e.stop();
        instance.selectListItem( null );
        return;
      }
    });
    this.searchBox.observe("keyup",function(e){
      if (!instance.options.keyValue)
      {
        instance.originalElement.value = instance.searchBox.value;
      }
      if (e.keyCode == Event.KEY_RETURN ||
          e.keyCode == Event.KEY_ESC ||
          e.keyCode == Event.KEY_UP ||
          e.keyCode == Event.KEY_DOWN)
      {
        e.stop();
        return
      }
      var items = instance.autoCompleteList.select("li");
      if (instance.searchBox.value.length < 1)
      {
        instance.autoCompleteList.hide();
        return;
      }
      if (this.lastSearch == instance.searchBox.value)
        return;
      this.lastSearch = instance.searchBox.value;
      instance.showAutocomplete(instance.searchBox.value);
    },this);
    
    if (this.originalElement.value.length > 0)
    {
      if (this.options.keyValue)
      {
        new Ajax.Request(options.dataUrl,{
          method: "post",
          parameters: $H({"id":this.originalElement.value}).toQueryString(),
          onSuccess: function(transport) 
          {
            instance.select( transport.responseJSON[0].id, transport.responseJSON[0].name );
          },
        });
      }
      else
      {
        this.searchBox.value = this.originalElement.value;
      }      
    }
  },
  reset:function(complete)
  {
    if (complete)
      this.searchBox.value = "";
    this.originalElement.value = "";
    if (this.finalSelection)
      this.finalSelection.hide();
    this.searchBox.show();  
  },
  selectListItem: function(li)
  {
    this.autoCompleteList.hide();
    if (li)
    {
      var item = li.retrieve("item");

      this.select( item.id, item.name );
    } 
    else
    {
      if (this.finalSelection)
        this.finalSelection.hide();
      this.searchBox.show();
    }
  },
  checkIfElementIsOurs: function(ev)
  {
    if (this.dropButton && ev.findElement(".autocompleteDropbutton") == this.dropButton) return true;
    if (ev.findElement(".autocompleteSearch") == this.searchBox) return true;
    if (ev.findElement(".autocompleteList") == this.autoCompleteList) return true;
    return false;
  },
  select: function( id, name )
  {
    this.originalElement.value = this.options.keyValue ? id : name;
    this.searchBox.value = name;
    if (this.finalSelection)
    {
      this.finalSelection.update("<span>" + name.escapeHTML() + "</span> (click to change)");
      this.finalSelection.show();
      this.searchBox.hide();
    }
    
    if (this.options.onSelectItem)
      this.options.onSelectItem(id);
  },
  showAutocomplete: function( term )
  {
    var instance = this;
    new Ajax.Request(this.options.dataUrl,{
      method: "post",
      parameters: $H({"search":term}).toQueryString(),
      onSuccess: function(transport) 
      {
        instance.keyboardSelection = -1;
        var results = $A( transport.responseJSON );
        if (!results.length) {
          instance.autoCompleteList.hide();
          return;
        }
        instance.autoCompleteList.show();
        instance.autoCompleteList.update("");
  
        instance.autoCompleteList.setStyle({
          "top" :(instance.searchBox.cumulativeOffset().top + instance.searchBox.getLayout().get("height") + 4) + "px",
          "left":instance.searchBox.cumulativeOffset().left + "px",
          "width":(instance.options.width == null ? instance.searchBox.getLayout().get("width") : parseInt(instance.options.width,10)) + "px",
        });
        results.each(function(item){
          var func = instance.options.processRow || function(item) { return item.name.escapeHTML() };
          var li = new Element("li").update( func(item) );
          li.store("item",item);
          li.observe("click",function(){
            instance.selectListItem(li);
          });
          instance.autoCompleteList.insert(li);
        });
      }
    });
  }
});
