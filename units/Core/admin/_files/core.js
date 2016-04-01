var _core = new _core_object();
function _core_object()
{
	var self = this;
	self.config = {};
	self.blocks = {};
	self.list_js = [];
	self.list_css = [];
	
	self.set = function(cfg)
	{
		$.extend(true, self.config, cfg);
	}
	
	self.set_blocks = function(blocks)
	{
		$.extend(true, self.blocks, blocks);
	}
	
	self.parent = function(b)
	{
		for (key in self.blocks)
		{
			for (i = 0, count = self.blocks[key].length; i < count; i++)
			{
				if (self.blocks[key][i]['block'] == b)
				{
					return key;
				}
			}
		}
		
		return false;
	}
	
	self.parent_config = function(b)
	{
		var config = {};
		var key = self.parent(b);
		
		if (key)
		{
			config = _block.config(key);
		}
		
		return config;
	}
	
	self.autoload = function()
	{
		for (var key in self.config)
		{
			if (self.config[key]['cfg'] && self.config[key]['cfg']['onload'])
			{
				self.load(self.config[key]['cfg']['onload']);
			}
		}
		
		self.loaded_js();
		self.loaded_css();
	}
	
	self.load = function(mas)
	{
		if (mas)
		{
			for (var i = 0, count = mas.length; i < count; i++)
			{
				if (window[mas[i]])
				{
					window[mas[i]]();
				}
			}
		}
	}

	self.loaded_js = function()
	{
		var scripts = document.getElementsByTagName('script');
		for (var i = 0, count = (scripts.length || 0); i < count; i++)
		{
			if (scripts[i].src && scripts[i].src != "")
			{
				self.list_js.push(scripts[i].src.replace(document.location.protocol + "//" + document.location.host, ""));
			}
		}
	}
	
	self.loaded_css = function()
	{
		var css = document.getElementsByTagName('link');
		for (var i = 0, count = (css.length || 0); i < count; i++)
		{
			if (css[i].rel && css[i].rel == "stylesheet")
			{
				self.list_css.push(css[i].href.replace(document.location.protocol + "//" + document.location.host, ""));
			}
		}
	}
}