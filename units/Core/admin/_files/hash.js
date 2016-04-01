lz.create("Hash");
function Hash()
{
	var self = this;
	
	self.list = [];
	
	onhashchange = function()
	{
		self.parse();
		self.load();
	}
	
	self.init = function()
	{
		self.parse();
	}
	
	self.parse = function()
	{
		self.list = lz.array.diff(window.location.hash.replace('#', '').split('/'), "");
		
		for (var i = 0, count = self.list.length; i < count; i++)
		{
			var tmp = lz.array.diff(self.list[i].split('&'), "");
			self.list[i] = {};
			
			for (var j = 0, count_els = tmp.length; j < count_els; j++)
			{
			 	var keys = lz.array.diff(tmp[j].split(':'), "");
			 	
			 	if (keys.length == 2)
			 	{
		 			self.list[i][keys[0]] = keys[1];
			 	}
			}
		}
	}
	
	self.load = function()
	{
		if (self.list.length)
		{
			var last = self.list[self.list.length - 1];
			
			if (last.block && last.unit)
			{
				lz.blocks.get_block(last.block, last.unit, last.edit);
			}
		}
		else
		{
			for (blocks_id in lz.blocks.list)
			{
				if (lz.blocks.list[blocks_id].items_list)
				{
					lz.blocks.list[blocks_id].print();
				}
			}
		}
	}
	
	self.print = function()
	{
		var hash_line = "";
		
		for (var i = 0, count = self.list.length; i < count; i++)
		{
			for (key in self.list[i])
			{
			 	hash_line += key + ":" + self.list[i][key] + "&";
			}
			
			hash_line = lz.trim(hash_line, "&");
			hash_line += "/";
		}
		
		window.location.hash = hash_line;
	}
	
	self.add = function(mas)
	{
		self.list.push(mas);
		self.print();
	}
	
	self.edit = function(unit, block, ops)
	{
		for (var i = 0, count = self.list.length; i < count; i++)
		{
			if (self.list[i].unit == unit && self.list[i].block == block)
			{
				for (var key in ops)
				{
					self.list[i][key] = ops[key];
				}
			}
		}
		
		self.print();
	}
	
	self.remove = function(mas)
	{
		self.list.pop();
		self.print();
	}
	
	self.get = function(unit, block)
	{
		for (var i = 0, count = self.list.length; i < count; i++)
		{
			if (self.list[i].unit == unit && self.list[i].block == block)
			{
				return self.list[i]; 
			}
		}
		
		return {};
	}
	
	self.get_this = function()
	{
		var count = self.list.length;
		
		if (count > 0)
		{
			return self.list[count - 1];
		}
		
		return {};
	}
}