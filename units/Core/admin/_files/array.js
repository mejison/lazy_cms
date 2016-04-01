lz.create("Array");
function Array()
{
	var self = this;
	
	self.unique = function(array)
	{
		array = array || [];
		var new_array = [];
		
		for (var key in array)
		{
			if ( ! self.has(array[key], new_array))
			{
				new_array.push(array[key]);
			}
		}

		return new_array;
	}
	
	self.has = function(el, array)
	{
		array = array || [];
		for (var key in array)
		{
			if (array[key] == el)
			{
				return true;
			}
		}
		
		return false;
	}
	
	self.diff = function(array, el, save_keys)
	{
		array = array || [];
		el = (typeof el != "object") ? [el] : el;
		save_keys = save_keys || false;
		
		var new_array = [];
		
		for (var key in array)
		{
			var found = false;
			for (var k in el)
			{
				if (array[key] == el[k])
				{
					found = true;
				}

				if ( ! found)
				{
					if (save_keys)
					{
						new_array[key] = array[key];
					}
					else
					{
						new_array.push(array[key]);
					}
				}
			}
		}

		return new_array;
	}
	
	self.count = function(object)
	{
		var count = 0;
		for (var key in object)
		{
			count++;
		}
		
		return count;
	}
}