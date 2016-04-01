var objLazyToolbar = {

    ConvertTermsToURI: function(terms)
    {
        var termArray = new Array();
        termArray = terms.split(" ");
        termArray.forEach(this.DoEncode);
        return termArray.join("+");
    },

    DoEncode: function(element, index, array)
    {
        array[index] = encodeURIComponent(element);
    },
    
    KeyHandler: function(event)
    {
        if(event.keyCode == event.DOM_VK_RETURN)
        {
			this.Search(event, 'web');
        }
    },

    LoadURL: function(url)
    {
        window.content.document.location = url;
        window.content.focus();
    },

    Search: function(event, system, type)
    {
        var URL = "";
        var isEmpty = false;
        var searchTermsBox = document.getElementById("Lazy-SearchTerms");
        var searchTerms = this.TrimString(searchTermsBox.value);
    
        if(searchTerms.length == 0)
        {
            isEmpty = true;
        }
        else
        {
			searchTerms = this.ConvertTermsToURI(searchTerms);
        }

		if (system == "google")
		{
	        switch(type)
	        {
	        	case "image":	if (isEmpty)
								{
									URL = "http://images.google.com/";
								}
	            				else
								{
									URL = "http://images.google.com/images?q=" + searchTerms;
								}
	            				break;
	
				case "web": 	if (isEmpty)
								{
									URL = "http://www.google.com.ua/";
								}
	            				else
								{
									URL = "http://www.google.com.ua/search?q=" + searchTerms;
								}
	            				break;
	            default: 		URL = "http://www.google.com.ua/"; break;
			}
		}
		
		if (system == "yandex")
		{
			switch(type)
	        {
	        	case "image":	if (isEmpty)
								{
									URL = "http://images.yandex.ua/";
								}
	            				else
								{
									URL = "http://images.yandex.ua/yandsearch?text=" + searchTerms;
								}
	            				break;
	
				 case "web": 	if (isEmpty)
								{
									URL = "http://yandex.ua/";
								}
	            				else
								{
									URL = "http://yandex.ua/yandsearch?text=" + searchTerms;
								}
	            				break;
	            default: 		URL = "http://yandex.ua/"; break;
			}
		}

        this.LoadURL(URL);
    },

    TrimString: function(string)
    {
        if ( ! string)
        {
            return "";
        }
    
        string = string.replace(/^\s+/, '');
        string = string.replace(/\s+$/, '');
        string = string.replace(/\s+/g, ' ');
    
        return string;
    },
    
    Populate: function()
	{
	    var menu = document.getElementById("Lazy-SearchTermsMenu");
	    for(var i = menu.childNodes.length - 1; i >= 0; i--)
	    {
	        menu.removeChild(menu.childNodes.item(i));
	    }

        var tempItem1 = document.createElement("menuitem");
        tempItem1.setAttribute("label", "Розробка сайтів Рівне");
        menu.appendChild(tempItem1);
        
        var tempItem2 = document.createElement("menuitem");
        tempItem2.setAttribute("label", "Просування сайтів у Рівному");
        menu.appendChild(tempItem2);
        
        var tempItem3 = document.createElement("menuitem");
        tempItem3.setAttribute("label", "Сайт за 1000 Рівне");
        menu.appendChild(tempItem3);
	},
	
	AddDynamicButtons: function()
	{
	    var container = document.getElementById("Lazy-DynButtonContainer");
	    for(i=container.childNodes.length; i > 0; i--)
		{
			container.removeChild(container.childNodes[0]);
	    }

	    for(var i = 0; i < 5; i++)
		{
	        var tempButton = null;
	        tempButton = document.createElement("toolbarbutton");
	        tempButton.setAttribute("label", "Button " + i);
	        tempButton.setAttribute("tooltiptext", "Button " + i);
	        container.appendChild(tempButton);
	    }
	},
};