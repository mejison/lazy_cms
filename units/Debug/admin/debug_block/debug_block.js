lz.blocks.create("Debug_block");
function Debug_block()
{
	var self = this;
	self.timer = 0;
	self.lifetime = 3000;
	
	self.init = function()
	{
		lz.debug.custom_show = self.custom_show;
		lz.debug.custom_history = self.custom_history;
		lz.debug.custom_clear_hints = self.custom_clear_hints;
		lz.debug.custom_remove_hint = self.custom_remove_hint;
	}
	
	self.custom_show = function(errors_list)
	{
		clearTimeout(self.timer);
		var count = errors_list.list.length;
		if (count)
		{
			var errors_box = lz.el.div('errors_box');
			lz.el.over(errors_box, self.errors_over, self, errors_box);
			lz.el.out(errors_box, self.errors_out, self, errors_box);
			
			var p_title = lz.el.p('errors_title');
			lz.el.append(lz.el.text(errors_list.title), p_title);
			lz.el.click(p_title, self.errors_close, self);
			
			var a_close = lz.el.a('', 'link_errors_close');
			lz.el.click(a_close, self.errors_close, self);
			
			var errors_block = lz.el.div('errors_block');
			lz.el.click(errors_block, self.errors_close, self);
			
			var type = 3;
			for (var i = 0; i < count; i++)
			{
				var error = errors_list.list[i];
				if (error.hint && error.hint != "")
				{
					self.custom_hint(error);
				}
				
				var p = lz.el.p('errors_' + error.type);
				lz.el.html(error.text, p);
				lz.el.append(p, errors_block);
				
				type = Math.min(type, error.type);
			}

			lz.el.append(p_title, errors_box);
			lz.el.append(a_close, errors_box);
			lz.el.append(errors_block, errors_box);
			
			var a_count = lz.el.a('', 'link_errors_count');
			lz.el.click(a_count, lz.debug.show_history, self);

			lz.el.empty(self.object);
			self.object.className = "debug_block type_" + type;
			lz.el.append(errors_box, self.object);
			lz.el.append(a_count, self.object);
		}
		self.timer = setTimeout(self.errors_close, self.lifetime);
	}
	
	self.custom_hint = function(error)
	{
		if (error.object)
		{
			var hint = lz.by_class("_hint", error.object)[0];
			var append = false;
			if ( ! hint)
			{
				hint = lz.el.p("_hint hints_" + error.type);
				append = true;
			}

			var hint_text = lz.el.span();
			lz.el.html(error.hint, hint_text);
			lz.el.append(hint_text, hint);
	
			var parent = error.object.parentNode;
			lz.el.add_class(parent, "error");
			if (append)
			{
				lz.el.append(hint, parent);
			}
		}
	}
	
	self.custom_clear_hints = function(errors)
	{
		for (var key in errors)
		{
			if (errors[key].object)
			{
				var hint = lz.by_class("_hint", errors[key].object.parentNode)[0];
				if (hint)
				{
					lz.el.remove(hint);
				}
			}
		}
	}
	
	self.custom_remove_hint = function(object)
	{
		var parent = object.parentNode;
		if (lz.el.has_class(parent, "error"))
		{
			var hint = lz.by_class("_hint", parent)[0];
			if (hint)
			{
				lz.el.remove(hint);
				lz.el.remove_class(parent, "error");
			}
		}
	}
	
	self.custom_history = function(history_list)
	{
		clearTimeout(self.timer);
		
		var errors_box = lz.el.div('errors_box');
		lz.el.over(errors_box, self.errors_over, self, errors_box);
		lz.el.out(errors_box, self.errors_out, self, errors_box);
		
		var p_title = lz.el.p('errors_title');
		lz.el.append(lz.el.text(lz.lng("history_title")), p_title);
		lz.el.click(p_title, self.errors_close, self);
		
		var a_close = lz.el.a('', 'link_errors_close');
		lz.el.click(a_close, self.errors_close, self);
		
		var errors_block = lz.el.div('errors_block');
		lz.el.click(errors_block, self.errors_close, self);
		
		var type = 3;
		for (var key in history_list)
		{
			var p_time = lz.el.p("errors_time");
			lz.el.html(key + " - " + history_list[key].title, p_time);
			lz.el.append(p_time, errors_block);
			
			for (var i = 0, count = history_list[key].list.length; i < count; i++)
			{
				var error = history_list[key].list[i];
				var p = lz.el.p('errors_' + error.type);
				lz.el.html(error.text, p);
				lz.el.append(p, errors_block);
			}
		}

		lz.el.append(p_title, errors_box);
		lz.el.append(a_close, errors_box);
		lz.el.append(errors_block, errors_box);
		
		var a_count = lz.el.a('', 'link_errors_count');
		lz.el.click(a_count, lz.debug.show_history, self);

		lz.el.empty(self.object);
		self.object.className = "debug_block type_" + type;
		lz.el.append(errors_box, self.object);
		lz.el.append(a_count, self.object);
		
		self.timer = setTimeout(self.errors_close, self.lifetime);
	}
	
	self.errors_over = function(event, el)
	{
		lz.el.add_class(el, "over");
		clearTimeout(self.timer);
	}
	
	self.errors_out = function(event, el)
	{
		lz.el.remove_class(el, "over");
		self.timer = setTimeout(self.errors_close, self.lifetime);
	}
	
	self.errors_close = function(event)
	{
		var errors_box = lz.by_class("errors_box", self.object)[0];
		if (errors_box)
		{
			lz.el.remove(errors_box);
		}
		
		clearTimeout(self.timer);
	}
}


function hide_errors()
{
	clearTimeout(_errors.timer);
	$('#errors_box').hide();
}

function over_errors()
{
	clearTimeout(_errors.timer);
	$('#errors_box').addClass("over_errors");
}

function out_errors()
{
	_errors.timer = setTimeout('_errors.hide()', errors_delay);
	$('#errors_box').removeClass("over_errors");
}

function focus_errors(object)
{
	var j_parent = $(object).parent("div");
	j_parent.removeClass("error");
	j_parent.children("div.debug_hint").remove();
}

function show_hints()
{
	var hint_objects = [];
	for (var i = 0, count = _errors.hints.length; i < count; i++)
	{
		var hint = _errors.hints[i];
		hint.object._lazy_hints = hint.object._lazy_hints || [];
		hint.object._lazy_hints.push(hint);
		
		hint_objects.push(hint.object);
	}
	
	for (i = 0, count = hint_objects.length; i < count; i++)
	{
		var hint = document.createElement("div");
		
		var hint_arrow = document.createElement("div");
		hint_arrow.className = "debug_hint_arrow";
		
		var hint_arrow_inner = document.createElement("div");
		hint_arrow.appendChild(hint_arrow_inner);
		
		hint.appendChild(hint_arrow);

		var p_hint = [];
		var p_class = 3;
		
		for (var j = 0, hints_count = hint_objects[i]._lazy_hints.length; j < hints_count; j++)
		{
			var items = hint_objects[i]._lazy_hints[j];
			
			p_hint[i] = document.createElement("p");
			p_hint[i].innerHTML = "&#8226;&nbsp;" + items.text;
			hint.appendChild(p_hint[i]);
			
			p_class = Math.min(p_class, items.type);
		}
		
		var j_parent = $(hint_objects[i]).parent("div");
		var parent_height = j_parent.outerHeight();
		var parent_width = j_parent.outerWidth();
		
		hint.className += "debug_hint hint_" + p_class;

		j_parent.children("div.debug_hint").remove();
		//j_parent.removeClass("focus");
		j_parent.addClass("error");
		
		hint_objects[i].parentNode.appendChild(hint);
		hint_objects[i]._lazy_hints = [];
	}
}