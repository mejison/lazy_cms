lz.create("Langs");
function Langs()
{
	var self = this;
	self.name = "Langs";
	
	self._this = "";
	self._default = {};
	self.client = [];
	self.client_list = false;
	self.admin = [];
	self.admin_list = false;

	self.init = function()
	{
		self._this = self._lazy._locals['this'];
		self._default = self._lazy._locals['default'];
		
		self.client = self._lazy._locals['client'] || [];
		self.admin = self._lazy._locals['admin'] || [];
	}
	
	self.default = function(type)
	{
		type = type || lz.type;
		return self._default[type] || false;
	}
	
	self.list = function(type)
	{
		type = type || lz.type;
		if ( ! self[type + '_list'])
		{
			self[type + '_list'] = [];
			for (var i = 0, count = self[type].length; i < count; i++)
			{
				self[type + '_list'].push(self[type][i].code);
			}
		}
		
		return self[type + '_list'];
	}
}