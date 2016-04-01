lz.create("Validate");
function Validate()
{
	var self = this;

	self.check = function(field)
	{
		var type = field.patern || field.object.type;
		field.object.value = lz.trim(field.object.value);
		return (self[type]) ? self[type](field) : self.text(field);
	}
	
	self.text = function(field)
	{
		var error = true;
		if (error = self.require(field))
		{
			error *= self.min(field);
			error *= self.max(field);
		}

		return error;
	}
	
	self.require = function(field)
	{
		if (field.object.value == "")
		{
			if (field.require && ( ! (field.object.dataset && ! field.object.dataset.lang) || (field.object.dataset.lang != lz.langs._default.client || field.object.dataset.lang != lz.langs._default.admin)))
			{
				lz.debug.add("empty_value", field);
				return false;
			}
		}

		return true;
	}

	self.min = function(field)
	{
		if (field.min > 0 && field.object.value.length < field.min)
		{
			lz.debug.add("min_text", field);
			return false;
		}
		
		return true;
	}
	
	self.max = function(field)
	{
		if (field.max > 0 && field.object.value.length > field.max)
		{
			lz.debug.add("max_text", field);
			return false;
		}
		
		return true;
	}
	
	self.checkbox = function(field)
	{
		if (field.require && ! field.object.checked)
		{
			lz.debug.add("empty_value", field);
			return false;
		}
		
		return true;
	}
	
	self.chars = function(field)
	{
		/*is_chars = config.fields_rules || "";
		var is_chars_array = [];
		if (is_chars != "")
		{
			for (var key in self.tpls)
			{
				if (is_chars.indexOf("[:" + key + "]") + 1)
				{
					is_chars_array = is_chars_array.concat(self.tpls[key]);
					is_chars = is_chars.replace("[:" + key + "]", "");
				}
			}
		}
		is_chars = is_chars_array.concat(self.validate_chars(is_chars));

		var chars = [];
		if (is_chars.length)
		{
			for (var i = 0, count = object.value.length; i < count; i++)
			{
				if ( ! _array.in(object.value.charAt(i), is_chars))
				{
					chars.push(object.value.charAt(i));
				}
			}
		}

		if (chars.length > 0)
		{
			var symbols = _array.unique(chars).join(", ");
			//self.add("symbols_value", {name: object.fieldName, symbols: symbols});
			//self.hint("symbols_value_hint", {symbols: symbols}, object);
			
			return false;
		}
		else
		{
			not_chars = "";
			var not_chars_array = [];
			not_chars = not_chars_array.concat(self.validate_chars(not_chars));
	
			if (not_chars.length)
			{
				for (i = 0; i < count; i++)
				{
					if (_array.in(object.value.charAt(i), not_chars))
					{
						chars.push(object.value.charAt(i));
					}
				}
			}
			
			if (chars.length > 0)
			{
				var symbols = _array.unique(chars).join(", ");
				//self.add("symbols_value", {name: object.fieldName, symbols: symbols});
				//self.hint("symbols_value_hint", {symbols: symbols}, object);
				
				return false;
			}
		}
		
		return true;*/
	}
	
	
	self.validate_chars = function(str)
	{
		var result = [];
		for (var i = 0, count = str.length; i < count; i++)
		{
			if (str.charAt(i) == "-")
			{
				if (str.charAt(i - 1) && str.charAt(i - 1) != "" && str.charAt(i - 1) != " " && str.charAt(i + 1) && str.charAt(i + 1) != "" && str.charAt(i + 1) != " ")
				{
					var from = str.charCodeAt(i - 1);
					var to = str.charCodeAt(i + 1);
					if (from < to)
					{
						result.pop();
						
						for (var j = from; j < to; j++)
						{
							result.push(String.fromCharCode(j));
						}
					}
				}
				else
				{
					result.push(str.charAt(i));
				}
			}
			else
			{
				result.push(str.charAt(i));
			}
		}
		
		return result;
	}

	self.validate_repeat = function(object) 
	{
		var repeat_objects = document.getElementById(object.dataset.repeat);
		if ($.trim(object.value) != $.trim(repeat_objects.value))
		{
			_errors.add("values_not_same");
			_errors.hint("values_not_same_hint", {}, object);

			return false;
		}
		
		return true;
	}
	
	
	
	self.validate_radio = function(object)
	{
		object.fieldName = $.trim((document.getElementsByClassName(object.id + "_p")[0]) ? document.getElementsByClassName(object.id + "_p")[0].innerHTML : "");
		
		var require = object.validate.require || false;
		if (require)
		{
			var radios = document.getElementsByName(object.name);
			
			var error = true;
			
			for (var i = 0, count = (radios.length || 0); i < count; i++)
			{
				if (radios[i].checked)
				{
					error = false;
				}
			}
			
			if (error)
			{
				self.add("empty_value");
				self.hint("empty_value_hint", {}, object);
				
				return false;
			}
		}
		
		return true;
	}
	
	self.validate_checkbox = function(object) 
	{
		object.fieldName = $.trim((document.getElementsByClassName(object.id + "_p")[0]) ? document.getElementsByClassName(object.id + "_p")[0].innerHTML : "");
		
		var require = object.validate.require || false;
		if (require && ! object.checked)
		{
			self.add("empty_value");
			self.hint("empty_value_hint", {}, object);
			return false;
		}

		return true;
	}
	
	self.validate_email = function(object)
	{
		var config = {"require": false};
		
		$.extend(true, config, object.validate);

		object.value = $.trim(object.value);
		object.fieldName = $.trim((document.getElementsByClassName(object.id + "_p")[0]) ? document.getElementsByClassName(object.id + "_p")[0].innerHTML : "");
	
		var error = true;
		if (error = self.require(object, config))
		{
			var reg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    		if ( ! reg.test(object.value))
    		{
				self.add("wrong_email");
				self.hint("wrong_email_hint", {}, object);
				
				error = false;
			}
		}

		return error;	
	}
	
	self.validate_money = function(object)
	{
		var config = {"min": 0,
					  "max": 0,
					  "is_chars": ",.[:num]",
					  "require": false};	
						
		$.extend(true, config, object.validate);
		
		object.value = $.trim(object.value);
		object.fieldName = $.trim((document.getElementsByClassName(object.id + "_p")[0]) ? document.getElementsByClassName(object.id + "_p")[0].innerHTML : "");
		
		var error = true;
		if (error = self.require(object, config))
		{
			error *= self.chars(object, config);
			if (!(!!(object.value.replace(',', '.') * 1)))
			{
				self.add("empty_value");
				self.hint("empty_value_hint", {}, object);
				error = false;
			}
			else
			{
				if (error)
				{
					if (config["min"] != 0)
					{
						if(object.value.replace(',', '.') < config["min"])
						{
							self.add("money_min", config);
							self.hint("money_min_hint", config, object);
							error = false;
						}
					}
					
					if (config["max"] != 0)
					{
						if(object.value.replace(',', '.') > config["max"])
						{
							self.add("money_max", config);
							self.hint("money_max_hint", config, object);
							error = false;
						}
					}
				}
			}
		}
		
		return error;
	}
}