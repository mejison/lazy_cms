lz.create("El");
function El()
{
	var self = this;
	self.name = "El";
	
	self.text = function(text)
	{
		return document.createTextNode(text);
	}
	
	self.p = function(class_name, id)
	{
		class_name = class_name || false;
		id = id || id;
		
		return self.create_element("p", class_name, id);
	}
	
	self.span = function(class_name, id)
	{
		class_name = class_name || false;
		id = id || id;
		
		return self.create_element("span", class_name, id);
	}
	
	self.a = function(url, class_name, id)
	{
		url = url || "";
		class_name = class_name || false;
		id = id || id;
		
		var a = self.create_element("a", class_name, id);
		a.href = (url == "") ? "javascript:void(0);" : url;
		
		return a;
	}
	
	self.div = function(class_name, id)
	{
		class_name = class_name || false;
		id = id || id;
		
		return self.create_element("div", class_name, id);
	}
	
	self.table = function(class_name, id)
	{
		class_name = class_name || false;
		id = id || id;
		
		return self.create_element("table", class_name, id);
	}
	
	self.tr = function(class_name, id)
	{
		class_name = class_name || false;
		id = id || id;
		
		return self.create_element("tr", class_name, id);
	}
	
	self.td = function(class_name, id)
	{
		class_name = class_name || false;
		id = id || id;
		
		return self.create_element("td", class_name, id);
	}
	
	self.select = function(list, class_name, id)
	{
		list = list || {};
		class_name = class_name || false;
		id = id || id;
		
		var select = self.create_element("select", class_name, id);
		for (var key in list)
		{
			var option = self.create_element("option");
			option.value = key;
			option.innerHTML = list[key];
			self.append(option, select);
		}
		
		return select;
	}
	
	self.select_list = function(select, list)
	{
		select = select || false;
		list = list || {};
		
		if (select && select.tagName.toLowerCase() == "select")
		{
			select.innerHTML = "";
			for (var key in list)
			{
				var option = self.create_element("option");
				option.value = key;
				option.innerHTML = list[key];
				self.append(option, select);
			}
		}

		return select;
	}
	
	self.button = function(name, class_name)
	{
		name = name || {};
		name = typeof name != "object" ? self.text(lz.lng("s_" + name)) : name;
		class_name = class_name || false;
		
		var button = self.a("", "button" + (class_name ? (" " + class_name) : ""));
		
		var button_top = self.span("button_top");
		self.append(button_top, button);
		
		var button_content = self.span("button_content");
		self.append(name, button_content);

		self.append(button_content, button);
		
		return button;
	}
	
	self.create_element = function(tag, class_name, id)
	{
		var object = document.createElement(tag);
		if (class_name && class_name != "")
		{
			object.className = class_name;
		}
		
		if (id && id != "")
		{
			object.id = id;
		}
		
		return object;
	}
	
	self.click = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;
		
		(function(object, action, parent, args)
		{
			object.onclick = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}

		})(object, action, parent, args);
	}
	
	self.dblclick = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.ondblclick = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.over = function()
	{
		var args = [];
		var temp = arguments;
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onmouseover = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.out = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onmouseout = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.focus = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onfocus = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.blur = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onblur = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.keypress = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onkeypress = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.keyup = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onkeyup = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.keydown = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onkeydown = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.mouseup = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onmouseup = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.mousedown = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onmousedown = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.load = function()
	{
		var args = [];
		for (var key in arguments)
		{
			args.push(arguments[key]);
		}
		
		var object = args.shift() || {};
		var action = args.shift() || false;
		var parent = args[0] || window;

		(function(object, action, parent, args)
		{
			object.onload = function(event)
			{
				if (action)
				{
					args[0] = event;
					action.apply(parent, args);
				}
			}
		})(object, action, parent, args);
	}
	
	self.append = function(object, parent)
	{
		parent = parent || lz.body();
		if (object && parent)
		{
			parent.appendChild(object);
		}
	}
	
	self.before = function(object, parent)
	{
		parent = parent || lz.body();
		if (object && parent && parent.childNodes)
		{
			parent.insertBefore(object, parent.childNodes[0]);
		}
	}
	
	self.html = function(value, object)
	{
		object = object || lz.body();
		if (value && parent)
		{
			if (value.outerHTML)
			{
				object.innerHTML = value.outerHTML;
			}
			else
			{
				object.innerHTML = value;
			}
		}
	}
	
	self.empty = function(object)
	{
		object = object || window;
		while(object.childNodes[0])
		{
			object.removeChild(object.childNodes[0]);
		}
	}
	
	self.remove = function(object)
	{
		object = object || window;
		if (object.parentNode)
		{
			object.parentNode.removeChild(object);
		}
	}
	
	self.add_class = function(object, class_name)
	{
		object = object || false;
		class_name = class_name || "";
		
		if (object && class_name != "")
		{
			var classes = object.className.split(" ");
			classes.push(class_name);
			object.className = lz.array.unique(classes).join(" ");
		}
	}
	
	self.remove_class = function(object, class_name)
	{
		object = object || false;
		class_name = class_name || "";
		
		if (object && class_name != "")
		{
			var classes = object.className.split(" ");
			object.className = lz.array.diff(classes, class_name).join(" ");
		}
	}
	
	self.has_class = function(object, class_name)
	{
		object = object || false;
		class_name = class_name || "";
		
		if (object && class_name != "")
		{
			var classes = object.className.split(" ");
			return lz.array.has(class_name, classes);
		}
		
		return false;
	}
	
	self.offset = function(object)
	{
		return object.getBoundingClientRect();
	}
	
	self.left = function(object)
	{
		object = object || document.documentElement;
		var rect = self.offset(object);
		var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft || lz.body().scrollLeft;
		var clientLeft = document.documentElement.clientLeft || lz.body().clientLeft || 0;
    	return Math.round(rect.left + scrollLeft - clientLeft);
	}
	
	self.top = function(object)
	{
		object = object || document.documentElement;
		var rect = self.offset(object);
		var scrollTop = window.pageYOffset || document.documentElement.scrollTop || lz.body().scrollTop;
		var clientTop = document.documentElement.clientTop || lz.body().clientTop || 0;
    	return Math.round(rect.top + scrollTop - clientTop);
	}
	
	self.width = function(object)
	{
		object = object || document.documentElement;
		var rect = self.offset(object);
    	return Math.round(rect.width);
	}
	
	self.height = function(object)
	{
		object = object || document.documentElement;
		var rect = self.offset(object);
    	return Math.round(rect.height);
	}
	
	self.scroll_left = function(object)
	{
		object = object || document.documentElement || lz.body();
    	return Math.round(window.pageXOffset || object.scrollLeft);
	}
	
	self.scroll_top = function(object)
	{
		object = object || document.documentElement || lz.body();
    	return Math.round(window.pageYOffset || object.scrollTop);
	}
}