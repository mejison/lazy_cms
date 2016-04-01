lz.blocks.create("Debug_time");
function Debug_time()
{
	var self = this;
	
	self.init = function()
	{
		lz.debug.custom_time = self.custom_time;
	}
	
	self.autoload = function()
	{
		lz.debug.set_time(lz._lazy.php_time);
	}
	
	self.custom_time = function(php, ajax)
	{
		php = php || 0;
		ajax = ajax || 0;
		
		if ( ! self.php_time)
		{
			self.php_time = lz.el.p("text_time");
			lz.el.append(self.php_time, self.object);
		}
		lz.el.html(lz.lng("php_time") + " = " + php + " " + lz.lng("seconds"), self.php_time);
		
		if ( ! self.ajax_time)
		{
			self.ajax_time = lz.el.p("text_time");
			lz.el.append(self.ajax_time, self.object);
		}
		lz.el.html(lz.lng("ajax_time") + " = " + ajax + " " + lz.lng("seconds"), self.ajax_time);
	}
}