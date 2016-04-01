lz.create("Fields");
function Fields()
{
	var self = this;
	self.list = {};
	
	self.info_box = false;
	
	self.set_focus = function(field)
	{
		field.object.focus();
		lz.el.add_class(field.object.parentNode, "focus");
	}
	
	self.focus = function(event, lang)
	{
		lang = lang || false;
		var field = this;
		var object = (lang) ? field[lang].object : field.object;
		var parent = object.parentNode;
		lz.el.add_class(parent, "focus");
		
		if (lz.trim(object.value) != "")
		{
			lz.el.add_class(parent, "filled");
		}
		
		self.info_show(field, lang);
	}
	
	self.blur = function(event, lang)
	{
		lang = lang || false;
		var field = this;
		var object = (lang) ? field[lang].object : field.object;
		var parent = object.parentNode;
		lz.el.remove_class(parent, "focus");
		
		if (lz.trim(object.value) == "")
		{
			lz.el.remove_class(parent, "filled");
		}
		else
		{
			lz.el.add_class(parent, "filled");
		}
		
		self.info_hide(field, lang);
	}
	
	self.keydown = function(event, lang)
	{
		lang = lang || false;
		var field = this;
		var object = (lang) ? field[lang].object : field.object;
		lz.debug.remove_hint(object);
	}
	
	self.info_show = function(field, lang)
	{
		if (field.info && lz.trim(field.info) != "")
		{
			if ( ! self.info_box)
			{
				self.info_box = lz.el.p("_info");
			}
			
			var object = (lang) ? field[lang].object : field.object;
			lz.el.html(field.info, self.info_box);
			lz.el.append(self.info_box, object.parentNode);
			
			self.info_config(self.info_box);
		}
	}
	
	self.info_hide = function(field, lang)
	{
		var object = (lang) ? field[lang].object : field.object;
		var info = lz.by_class("_info", object.parentNode);
		if (info)
		{
			lz.el.remove(info[0]);
		}
	}
	
	self.info_config = function(object)
	{
		object.className = "_info";
		
		var style = window.getComputedStyle(object, null);
		var max_width = style.maxWidth.replace("px", "") * 1;
		object.style.width = "auto";
		
		lz.el.add_class(object, "_reset");
		if (object.clientWidth > max_width)
		{
			lz.el.add_class(object, "_dynamic");
			object.style.width = max_width + "px";
		}
		lz.el.remove_class(object, "_reset");

		var client_width = lz.el.width();
		var width = lz.el.width(object);
		var left = lz.el.left(object);
		
		if ((left + width - lz.el.scroll_left()) > client_width)
		{
			lz.el.add_class(object, "_left");
			if ((lz.el.left(object) - lz.el.scroll_left()) < 0)
			{
				lz.el.remove_class(object, "_left");
				object.style.width = "auto";
				lz.el.add_class(object, "_top");
			}
		}
	}
	
	self.get_field = function(code, lang, block)
	{
		block = block || false;
		lang = lang || false;
		if ( ! block)
		{
			var config = lz.hash.get_this();
			block = config.block || false
		}
		
		if (block)
		{
			for (var key in lz.blocks.list)
			{
				if (lz.blocks.list[key].block == block)
				{
					for (var k in lz.blocks.list[key].fields)
					{
						if (lz.blocks.list[key].fields[k].code == code)
						{
							if ( ! lang && lz.blocks.list[key].fields[k].object)
							{
								return lz.blocks.list[key].fields[k].object;
							}
							else
							{
								if (lang && lz.blocks.list[key].fields[k][lang] && lz.blocks.list[key].fields[k][lang].object)
								{
									return lz.blocks.list[key].fields[k][lang].object;
								}
								
								return false;
							}
						}
					}
				}
			}
		}
		
		return false;
	}
	
	self.set_value = function(object, value)
	{
		value = value || "";
		
		if (object.type == "checkbox")
		{
			object.checked = (value != "" && value * 1) ? true : false;
		}
		else
		{
			if (object.type == "select-one")
			{
				for (j = 0, count_ops = object.childNodes.length; j < count_ops; j++)
				{
					if (object.childNodes[j].value == value)
					{
						object.childNodes[j].selected = true;
					}
				}
				
				object.value = value;
				
				if (value != "")
				{
					lz.el.add_class(object.parentNode, 'filled');
				}
				else
				{
					lz.el.remove_class(object.parentNode, 'filled');
				}
			}
			else
			{
				object.value = value;
				
				if (value != "")
				{
					lz.el.add_class(object.parentNode, 'filled');
				}
				else
				{
					lz.el.remove_class(object.parentNode, 'filled');
				}
			}
		}
	}
}