lz.blocks.create("Access_signout")
function Access_signout()
{
	var self = this;
	
	self.autoload = function()
	{
		var button = lz.by_id('link_signout');
		if (button)
		{
			lz.el.click(button, this.submit, this);
		}
	}
	
	self.submit_callback = function(data)
	{
		lz.wait();
		lz.ajax.add("admins", "logout", data, self.access_result);
		lz.ajax.send();
	}
	
	self.access_result = function(data)
	{
		window.location.reload(true);
	}
}