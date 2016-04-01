lz.create("Debug");
function Debug()
{
	var self = this;
	self.name = "Debug";
	
	self.errors = {};
	self.hints = {};
	self.list = [];
	self.history = {};
	
	self.this_title = "";
	
	self.custom_show = false;
	self.custom_history = false;
	self.custom_clear_hints = false;
	
	self.init = function()
	{
		self.errors = lz._lazy._errors || {};
		self.hints = lz._lazy._hints || {};
		self.list = lz._lazy.errors || {};
		self.show();
	}
	
	self.add = function(code, field, values)
	{
		values = values || {};
		field = field || false;
		
		if ( ! self.errors[code])
		{
			values = {code: code};
			code = "errors_empty";
		}
		
		if (self.errors[code])
		{
			var error = {code: code,
						 text: self.errors[code].text,
						 type: self.errors[code].type};
						 
			if (field)
			{
				values.name = field.name;
				if (self.hints[code])
				{
					error.object = field.object;
					error.hint = self.hints[code].text;
				}
			}
			
			for (var key in values)
			{
				error.text = error.text.replace("[:" + key + "]", values[key]);
			}
	
			self.list.push(error);
		}
	}
	
	self.title = function(code)
	{
		if ( ! self.errors[code])
		{
			values = {code: code};
			code = "title_empty";
		}
		
		if (self.errors[code])
		{
			self.this_title = self.errors[code].text;
		}
	}
	
	
	self.show = function(code, field, values)
	{
		code = code || false;
		values = values || false;
		field = field || false;
		
		if (code)
		{
			self.add(code, field, values);
		}
		
		if (self.list.length)
		{
			var date = new Date();
			var key = lz.zero(date.getHours()) + ":" + lz.zero(date.getMinutes()) + ":" + lz.zero(date.getSeconds());
			self.history[key] = {};
			self.history[key]['title'] = self.this_title;
			self.history[key]['list'] = self.list;
			
			for (var k in self.history[key]['list'])
			{
				if (self.history[key]['list'][k].values && self.list[k].values.object)
				{
					if (self.hints[self.history[key]['list'][k].key])
					{
						object = lz.fields.get_field(self.list[k].values.object, self.list[k].values.lang);
						
						if (object)
						{
							self.history[key]['list'][k].object = object;
							self.history[key]['list'][k].hint = self.hints[self.history[key]['list'][k].key].text;
						}
					}
				}
			}
			
			if (self.custom_clear_hints)
			{
				self.custom_clear_hints(self.list);
			}
			
			if (self.custom_show)
			{
				self.custom_show(self.history[key]);
			}
			else
			{
				self._debug(self.history[key]);
			}
		}
		
		self.list = [];
	}
	
	self.show_history = function()
	{
		if (self.custom_history)
		{
			self.custom_history(self.history);
		}
		else
		{
			self._debug(self.history);
		}
	}
	
	self.remove_hint = function(object)
	{
		if (self.custom_remove_hint)
		{
			self.custom_remove_hint(object);
		}
	}
	
	self.set_time = function(php, ajax)
	{
		if (self.custom_time)
		{
			self.custom_time(php, ajax);
		}
	}
}