lz.create("Scripts");
function Scripts()
{
	var self = this;
	self.name = "Scripts";
	
	self.test = function(event, text, i)
	{
		alert(text + " - " + i);
	}
	
	self.begin = function()
	{
		for (var i = 0; i < 5; i++)
		{
			var p = lz.el.a("", "link", "link_" + i);
			lz.el.append(lz.el.text("Hello " + i), p);
			lz.el.mouseup(p, self.test, "test", i);
			lz.el.append(p);
		}
	}
	
	self.end = function()
	{
		for (var i = 0; i < 5; i++)
		{
			var p = lz.el.a("", "link", "link_" + i);
			lz.el.append(lz.el.text("Hello " + i), p);
			lz.el.mouseup(p, self.test, "test", i);
			lz.el.append(p);
		}
	}
}