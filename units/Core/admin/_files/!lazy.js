var lz = new Lazy();
function Lazy()
{
	var self = this;
	self.name = "Lazy";
	self.count = 0;
	self.type = "";
	
	self._lazy = {};
	self._config = {};
	self._autoload = [];
	
	self.page_obj = false;
	self.body_obj = false;
	
	self.lazy = function(object)
	{
		self._lazy = object;
		var scripts = self.by_tag("script");
		for (var i = 0, count = scripts.length; i < count; i++)
		{
			if (scripts[i].getAttribute("data-remove") && scripts[i].getAttribute("data-remove") == "remove")
			{
				self.remove(scripts[i]);
			}
		}
		
		self.type = self._lazy._type;
		
		if (self._lazy._config)
		{
			self._config = self._lazy._config;
			for (var key in self._config)
			{
				if (self._config[key] && self._config[key].cfg && self._config[key].cfg.autoload)
				{
					for (var i = 0, count = self._config[key].cfg.autoload.length; i < count; i++)
					{
						self._autoload[key.toLowerCase()] = self._autoload[key.toLowerCase()] || [];
						self._autoload[key.toLowerCase()].push(self._config[key].cfg.autoload[i]);
					}
				}
			}
			
			//self.autoload();
		}
		
		window['_debug'] = self._debug;
	}
	/*
	self.autoload = function()
	{
		window.onload = function()
		{
			for (var key in self._autoload)
			{
				for (var i = 0, count = self._autoload[key].length; i < count; i++)
				{
					self[key][self._autoload[key][i]]();
				}
			}
		}
	}*/
	
	self.config = function(key)
	{
		if (self._config[this.name].cfg && self._config[this.name].cfg[key])
		{
			return self._config[this.name].cfg[key];
		}
		
		return false;
	}
	
	self.create = function(unit)
	{
		var unit_var = unit.toLowerCase();
		if ( ! self[unit_var])
		{
			window[unit].prototype = self;
			window[unit].prototype.constructor = window[unit];
			
			self[unit_var] = new window[unit];
			self[unit_var].name = unit;
			
			if (self[unit_var].init)
			{
				self[unit_var].init();
			}
		}
	}
	
	self.page = function()
	{
		if ( ! self.page_obj)
		{
			self.page_obj = self.by_id('container');
		}
		
		return self.page_obj || false;
	}
	
	self.body = function()
	{
		if ( ! self.body_obj)
		{
			self.body_obj = document.body || document.getElementsByTagName("body")[0];
		}
		
		return self.body_obj || false;
	}
	
	self.by_tag = function(value, object)
	{
		object = object || document;
		var temp = object.getElementsByTagName(value);
		if (temp && temp.length)
		{
			return temp;
		}
		return [];
	}
	
	self.by_class = function(value, object)
	{
		object = object || document;
		var temp = object.getElementsByClassName(value);
		if (temp && temp.length)
		{
			return temp;
		}

		return [];
	}
	
	self.by_name = function(value, object)
	{
		object = object || document;
		var temp = object.getElementsByName(value);
		if (temp && temp.length)
		{
			return temp;
		}
		return [];
	}
	
	self.by_id = function(value, object)
	{
		object = object || document;
		var temp = object.getElementById(value);
		if (temp)
		{
			return temp;
		}
		return false;
	}
	
	self.remove = function(object)
	{
		if (object)
		{
			object.parentNode.removeChild(object);
		}
	}
	
	self.lng = function(key)
	{
		if (self._lazy._langs && self._lazy._langs[key])
		{
			return self._lazy._langs[key];
		}
		
		return "";
	}
	
	self.extend = function(default_object, object)
	{
		object = object || {};
		
		var temp = {};
		for (var key in default_object)
		{
			temp[key] = (typeof default_object[key] == "object") ? self.extend(default_object[key]) : default_object[key];
		}
		
		for (var key in object)
		{
			temp[key] = (typeof object[key] == "object") ? self.extend(temp[key] || {}, object[key]) : object[key];
		}
		
		return temp;
	}
	
	self.zero = function(number)
	{
		number = (number || 1) * 1;
		return (number < 10 && number >= 0) ? ("0" + number) : number;
	}
	
	self.trim = function(str, charlist)
	{
		charlist =  ! charlist ? ' \xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
		var re = new RegExp('^[' + charlist + ']+|[' + charlist + ']+$', 'g');
		return str.replace(re, '');
	}
	
	self._debug = function(content, limit, title)
	{
		limit = limit || 3;
		title = title || self.lng("debug_title");
		
		var type = typeof content;

		var table = self.el.table();

		var tr_first = self.el.tr();
		
		var td_text = self.el.td("td_debug_text");
		var p_text = self.el.p();
		self.el.append(self.el.text(self.lng("debug_type") + ":"), p_text);
		self.el.append(p_text, td_text);

		var td_value = self.el.td("td_debug_value");
		var p_value = self.el.p();
		self.el.append(self.el.text(type), p_value);
		self.el.append(p_value, td_value);
		
		self.el.append(td_text, tr_first);
		self.el.append(td_value, tr_first);
		self.el.append(tr_first, table);

		var tr_second = self.el.tr();
		
		var td_text = self.el.td("td_debug_text");
		var p_text = self.el.p();
		self.el.append(self.el.text(self.lng("debug_value") + ":"), p_text);
		self.el.append(p_text, td_text);
		
		var td_value = self.el.td("td_debug_value");
		if (type == "object")
		{
			self.el.append(self._table(content, limit), td_value);
		}
		else
		{
			var p_value = self.el.p();
			self.el.append(self.el.text(content), p_value);
			self.el.append(p_value, td_value);
		}
		
		self.el.append(td_text, tr_second);
		self.el.append(td_value, tr_second);
		self.el.append(tr_second, table);
		
		var block = lz.blocks.system(table, self.lng("debug_title"));
		lz.blocks.open(block);
	}
	
	self._table = function(object, limit, level)
	{
		level = level || 1;

		var table = self.el.table("table_debug");

		for (var key in object)
		{
			var type = typeof object[key];
			
			var tr = self.el.tr();
			
			var td_text = self.el.td("td_debug_text");
			var p_text = self.el.p();
			self.el.append(self.el.text("[ " + key + " ] =>"), p_text);
			self.el.append(p_text, td_text);
			
			var td_value = self.el.td("td_debug_value");
			if (type == "object")
			{
				if (level < limit)
				{
					self.el.append(self._table(object[key], limit, (level + 1)), td_value);
				}
				else
				{
					var p_value = self.el.p();
					self.el.append(self.el.text(type), p_value);
					self.el.append(p_value, td_value);
				}
			}
			else
			{
				var p_value = self.el.p();
				self.el.append(self.el.text(object[key]), p_value);
				self.el.append(p_value, td_value);
			}
			
			self.el.append(td_text, tr);
			self.el.append(td_value, tr);
			self.el.append(tr, table);
		}

		return table;
	}
	
	self.wait = function()
	{
		if ( ! self._wait)
		{
			self._wait = lz.el.div("_wait");
			var p_wait = lz.el.p();
			lz.el.append(lz.el.text(lz.lng("wait")), p_wait);
			lz.el.append(p_wait, self._wait)
		}
		
		lz.el.append(self._wait);
	}
	
	self.hide = function()
	{
		if (self._wait)
		{
			lz.el.remove(self._wait);
		}
	}
}