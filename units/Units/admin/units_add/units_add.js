lz.blocks.create("Units_add");
function Units_add()
{
	var self = this;

	self.submit_callback = function(data)
	{
		lz.ajax.add("units", "save", data, self.result);
		lz.ajax.send();
	}
	
	self.result = function(data)
	{
		
	}
}