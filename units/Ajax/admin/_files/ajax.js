lz.create("Ajax");
function Ajax()
{
	var self = this;
	
	self.get_object = function()
	{
		if ( ! self.object)
		{
			if (typeof XMLHttpRequest === 'undefined') 
			{
				XMLHttpRequest = function() 
				{
					try { self.object = new ActiveXObject("Msxml2.XMLHTTP.6.0"); }
					catch(e) { }
					
					try { self.object = new ActiveXObject("Msxml2.XMLHTTP.3.0"); }
					catch(e) { }
					
					try { self.object = new ActiveXObject("Msxml2.XMLHTTP"); }
					catch(e) { }
					
					try { self.object = new ActiveXObject("Microsoft.XMLHTTP"); }
					catch(e) { }
					
					throw new Error("This browser doesn't support XMLHttpRequest");
			    };
		    }
		    
			self.object = new XMLHttpRequest();
		}

		return self.object;
	}
	
	self.object = self.get_object();
	self.list = {};
	self.random_ids = [];
	self.callbacks = {};
	
	self.close = false;
	self.timer = 0;
	self.start_time = 0;
	self.finish_time = 0;
	self.interval = 1000;
	self.lifetime = 30000;
	
	self.add = function(unit, method, data, callback)
	{
		var id = self.random_id();
		self.list[id] = {unit: unit, method: method, id: id, data: data, callback: callback};
		
		return id;
	}
	
	self.random_id = function()
	{
		var id = Math.floor((Math.random() * 9999) + 1);
		var check = true;
		while (check)
		{
			if (lz.array.has(id, self.random_ids))
			{
				id = Math.floor((Math.random() * 9999) + 1);
			}
			else
			{
				check = false;
			}
		}
		
		self.random_ids.push(id);
		
		return id;
	}
	
	self.send = function()
	{
		var count = self.random_ids.length;
		if (count > 0)
		{
			self.callbacks = {};
			var post_mas = [];
			
			for (var i = 0; i < count; i++)
			{
				self.callbacks[self.random_ids[i]] = self.list[self.random_ids[i]].callback;
				post_mas.push(self.list[self.random_ids[i]]);
				delete self.list[self.random_ids[i]].callback;
			}
			
			self.random_ids = [];
			self.list = {};
			
			if (post_mas.length > 0)
			{
				if ( ! self.close)
				{
					self.start();
					var url = window.location.protocol + "//" + window.location.host + "/" + lz._lazy._path + "/" + lz.langs._this + "/ajax/";
					post_mas = encodeURIComponent(JSON.stringify(post_mas));
				
					self.object.onreadystatechange = function()
					{
						if (self.object.readyState == 4)
						{
							if (self.object.status == 200)
							{
								var response = self.response(self.object.responseText);
								self.result(eval("(" + response + ")"));
							}
							else
							{
								lz.hide();
								switch (self.object.status)
								{
									case 404: _alert("404 - Requested URL is not found"); break;
									case 403: _alert("403 - Access denied"); break;
									default:  _alert(self.object.status + " - Current status"); break;
								}
							}
						}
					};
					
					self.object.open('POST', url, true);
					self.object.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
					self.object.setRequestHeader("Connection", "close");
					self.object.send("post_mas=" + post_mas);
				}
			}
			else
			{
				self.result();
			}
		}
	}
	
	self.response = function(data)
	{
		var result = data.split("ajax::");
		var debug_string = result[0];
		
		if (debug_string != "")
		{
			var php_debug = [];
			var check = true;
			while (check)
			{
				start = 0;
				if (start = (debug_string.indexOf("debug::") + 1))
				{
					var debug_mess = debug_string.substr(start - 1, debug_string.indexOf("::end") - start + 6);
					php_debug.push(debug_mess.replace("debug::", "").replace("::end", ""));
					debug_string = debug_string.replace(debug_mess, "");
				}
				else
				{
					check = false;
				}
			}

			for (var i = php_debug.length - 1; i >= 0; i--)
			{
				_debug(eval("(" + php_debug[i] + ")"));
			}

			if (debug_string != "")
			{
				_debug(debug_string, lz.lng("php_error_title"));
			}
		}

		var result_string = result[1];
		
		var check = true;
		while (check)
		{
			start = 0;

			if (start = (result_string.indexOf("debug::") + 1))
			{
				var debug_mess = result_string.substr(start - 1, result_string.indexOf("::end") - start + 6);
				result_string = result_string.replace(debug_mess, "");
			}
			else
			{
				check = false;
			}
		}

		return result_string;
	}
	
	self.start = function()
	{
		var date = new Date();
		self.start_time = date.getTime();
		self.timer = setInterval(self.check, self.interval);
		
		self.close = true;
	}
	
	self.check = function()
	{
		var date = new Date();
		if ((date.getTime() - self.start_time) >= self.lifetime)
		{
			self.finish(self.lifetime);
			
			self.hide();
			lz.debug.add("ajax_lost");
			lz_debug.show();
		}
	}
	
	self.finish = function(time)
	{
		var date = new Date();
		self.finish_time = date.getTime();
		clearTimeout(self.timer);
		
		lz.debug.set_time(time, self.finish_time - self.start_time);
		
		self.start_time = 0;
		self.finish_time = 0;
	}
	
	self.result = function(result)
	{
		delete self.object;
		self.object = self.get_object();
		
		self.close = false;
		result = result || false;

		if (result)
		{
			self.finish(result.time);
			var errors = [];
			
			for (var key in self.callbacks)
			{
				errors = errors.concat(result.data[key].errors);
				var callback = self.callbacks[key];
				
				if (result.data[key].result && callback)
				{
					callback(result.data[key].content);				
				}
			}
			
			lz.debug.list = errors;
			lz.debug.show();
		}
	}
}