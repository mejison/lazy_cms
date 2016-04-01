lz.blocks.create("Access_signin")
function Access_signin()
{
	var self = this;
	
	self.autoload = function()
	{
		if (self.fields[0])
		{
			lz.fields.set_focus(self.fields[0]);
		}
	}
	
	self.submit_callback = function(data)
	{
		lz.wait();
		lz.ajax.add("admins", "login", data, self.access_result);
		lz.ajax.send();
	}
	
	self.access_result = function(data)
	{
		window.location.reload(true);
	}
}