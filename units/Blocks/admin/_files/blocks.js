lz.create("Blocks");
function Blocks()
{
	var self = this;
	self.tree = {};
	self.list = {};
	self.files_count = 0;
	
	self.init = function()
	{
		if (lz._lazy._tree)
		{
			self.tree = lz._lazy._tree;
		}
	}

	self.create = function(block)
	{
		var block_var = block.toLowerCase();
		for (var parent in self.tree)
		{
			for (var i = 0, count = self.tree[parent].length; i < count; i++)
			{
				if (self.tree[parent][i].folder.toLowerCase() == block_var)
				{
					if ( ! self.list[self.tree[parent][i].id] || ! self.list[self.tree[parent][i].id].loaded)
					{
						var params = false;
						if (self.list[self.tree[parent][i].id])
						{
							params = self.list[self.tree[parent][i].id];
						}
						
						window[block].prototype = self;
						window[block].prototype.constructor = window[block];
						
						self.list[self.tree[parent][i].id] = new window[block];
						
						if (params)
						{
							for (var key in params)
							{
								self.list[self.tree[parent][i].id][key] = params[key];
							}
						}
						
						self.list[self.tree[parent][i].id].name = block;
						self.list[self.tree[parent][i].id].id = self.tree[parent][i].id;
						self.list[self.tree[parent][i].id].parent = parent;
						self.list[self.tree[parent][i].id].loaded = false;
						
						if (self.list[self.tree[parent][i].id].init)
						{
							self.list[self.tree[parent][i].id].init();
						}
					}
				}
			}
		}
		
		self.afterload();
	}
	
	self.afterload_check = true;
	self.afterload = function()
	{
		if (self.afterload_check)
		{
			self.afterload_check = false;
			window.onload = function()
			{
				for (var key in self.list)
				{
					self.list[key].object = lz.by_id(key);
					self.list[key].loaded = true;
					self.list[key].set_fields();
					if (self.list[key].autoload)
					{
						self.list[key].autoload();
					}
				}
				
				lz.hash.load();
			}
		}
		else
		{
			if (document.readyState == "complete")
			{
				for (var key in self.list)
				{
					if ( ! self.list[key].loaded && self.list[key].object)
					{
						self.list[key].set_fields();
						if (self.list[key].autoload)
						{
							self.list[key].autoload();
						}
					}
					
					self.list[key].loaded = true;
				}
			}
		}
	}
	
	self.set_fields = function()
	{
		if (this.object)
		{
			this.fields = [];
			var inputs = lz.by_tag('input', this.object);
			if (inputs)
			{
				for (var i = 0, count = inputs.length; i < count; i++)
				{
					if (lz.fields.list[this.id] && lz.fields.list[this.id]['fields_config'] && lz.fields.list[this.id]['fields_config'][inputs[i].name])
					{
						lz.el.keypress(inputs[i], this.submit, this);
						
						if ((lz.fields.list[this.id]['fields_config'][inputs[i].name]['langs'] == "client" || lz.fields.list[this.id]['fields_config'][inputs[i].name]['langs'] == "admin") && inputs[i].dataset.lang)
						{
							if ( ! this.this_lang)
							{
								this.this_lang = lz.langs._default[lz.fields.list[this.id]['fields_config'][inputs[i].name]['langs']];
							}
							
							this.for_langs = lz.fields.list[this.id]['fields_config'][inputs[i].name]['langs'];
							
							lz.el.focus(inputs[i], lz.fields.focus, lz.fields.list[this.id]['fields_config'][inputs[i].name], inputs[i].dataset.lang);
							lz.el.blur(inputs[i], lz.fields.blur, lz.fields.list[this.id]['fields_config'][inputs[i].name], inputs[i].dataset.lang);
							lz.el.keydown(inputs[i], lz.fields.keydown, lz.fields.list[this.id]['fields_config'][inputs[i].name], inputs[i].dataset.lang);
						
							lz.fields.list[this.id]['fields_config'][inputs[i].name][inputs[i].dataset.lang] = {};
							lz.fields.list[this.id]['fields_config'][inputs[i].name][inputs[i].dataset.lang].object = inputs[i];
						}
						else
						{
							lz.el.focus(inputs[i], lz.fields.focus, lz.fields.list[this.id]['fields_config'][inputs[i].name]);
							lz.el.blur(inputs[i], lz.fields.blur, lz.fields.list[this.id]['fields_config'][inputs[i].name]);
							lz.el.keydown(inputs[i], lz.fields.keydown, lz.fields.list[this.id]['fields_config'][inputs[i].name]);
							lz.fields.list[this.id]['fields_config'][inputs[i].name].object = inputs[i];
						}
						
						this.fields.push(lz.fields.list[this.id]['fields_config'][inputs[i].name]);
					}
				}
			}
			
			var selects = lz.by_tag('select', this.object);
			
			if (selects)
			{
				for (var i = 0, count = selects.length; i < count; i++)
				{
					if (lz.fields.list[this.id] && lz.fields.list[this.id]['fields_config'] && lz.fields.list[this.id]['fields_config'][selects[i].name])
					{
						lz.el.keypress(selects[i], this.submit, this);
						
						if ((lz.fields.list[this.id]['fields_config'][selects[i].name]['langs'] == "client" || lz.fields.list[this.id]['fields_config'][selects[i].name]['langs'] == "admin") && selects[i].dataset.lang)
						{
							if ( ! this.this_lang)
							{
								this.this_lang = lz.langs._default[lz.fields.list[this.id]['fields_config'][selects[i].name]['langs']];
							}
							
							this.for_langs = lz.fields.list[this.id]['fields_config'][inputs[i].name]['langs'];
							
							lz.el.focus(selects[i], lz.fields.focus, lz.fields.list[this.id]['fields_config'][selects[i].name], selects[i].dataset.lang);
							lz.el.blur(selects[i], lz.fields.blur, lz.fields.list[this.id]['fields_config'][selects[i].name], selects[i].dataset.lang);
							
							lz.fields.list[this.id]['fields_config'][selects[i].name][selects[i].dataset.lang] = {};
							lz.fields.list[this.id]['fields_config'][selects[i].name][selects[i].dataset.lang].object = selects[i];
						}
						else
						{
							lz.el.focus(selects[i], lz.fields.focus, lz.fields.list[this.id]['fields_config'][selects[i].name]);
							lz.el.blur(selects[i], lz.fields.blur, lz.fields.list[this.id]['fields_config'][selects[i].name]);
							lz.fields.list[this.id]['fields_config'][selects[i].name].object = selects[i];
						}
						this.fields.push(lz.fields.list[this.id]['fields_config'][selects[i].name]);
					}
				}
			}
			
			var buttons = lz.by_class('submit', this.object);
			if (buttons)
			{
				for (var i = 0, count = buttons.length; i < count; i++)
				{
					lz.el.click(buttons[i], this.submit, this);
				}
			}
		}
	}
	
	self.submit = function(event)
	{
		event = event || window.event || false;
		if (event && ((event.type == "click") || (event.type == "keypress" && event.keyCode == 13)))
		{
			lz.debug.title(this.name.toLowerCase() + "_title");
			
			var error = true;
			var data = {};

			for (var key in this.fields)
			{
				if (this.fields[key].langs == "admin" || this.fields[key].langs == "client")
				{
					for (var j = 0, count_langs = lz.langs[this.fields[key].langs].length; j < count_langs; j++)
					{
						error *= lz.validate.check(this.fields[key][lz.langs[this.fields[key].langs][j].code]);
				
						if (this.fields[key][lz.langs[this.fields[key].langs][j].code].object.name)
						{
							if ( ! data[this.fields[key][lz.langs[this.fields[key].langs][j].code].object.name])
							{
								data[this.fields[key][lz.langs[this.fields[key].langs][j].code].object.name] = {};
							}

							data[this.fields[key][lz.langs[this.fields[key].langs][j].code].object.name][lz.langs[this.fields[key].langs][j].code] = this.fields[key][lz.langs[this.fields[key].langs][j].code].object.value;
						}
					}
				}
				else
				{
					error *= lz.validate.check(this.fields[key]);
				
					if (this.fields[key].object.name)
					{
						if (this.fields[key].object.type == "checkbox")
						{
							data[this.fields[key].object.name] = ( ! this.fields[key].object.checked) ? 0 : 1;
						}
						else
						{
							data[this.fields[key].object.name] = this.fields[key].object.value;
						}
					}
				}
			}
			
			lz.debug.show();
			if (error)
			{
				if (this.submit_callback)
				{
					data.id = this.edit || false;
					
					if (data.id)
					{
						this.edit_data[data.id] = data;
						for (var key in self.list)
						{
							if (self.list[key].add_block && self.list[key].add_block == this.block && self.list[key].items_list)
							{
								for (var k in self.list[key].items_list)
								{
									if (self.list[key].items_list[k].id == data.id)
									{
										self.list[key].items_list[k] = data;
									}
								}
							}
						}
					}
					
					this.submit_callback(data);
				}
			}
		}
	}
	
	self.z_index = 500;
	self.system = function(content, title, buttons)
	{
		content = content || "";
		content = (typeof content != "object") ? lz.el.text(content) : content;
		title = title || "";
		buttons = buttons || [{name: "close", class_name: "submit"}];
		
		var block = lz.el.div("system_block");
		block.style.zIndex = self.z_index + 1;
		
		var title_box = lz.el.div("system_title");
		var p_title = lz.el.p("text_system_title");
		lz.el.append(lz.el.text(title), p_title);
		lz.el.append(p_title, title_box);
		
		var a_title = lz.el.a("", "link_system_title");
		lz.el.click(a_title, self.system_close, self, block);
		lz.el.append(a_title, title_box);
		
		lz.el.append(title_box, block);

		var content_box = lz.el.div("system_content");
		lz.el.append(content, content_box);
		
		lz.el.append(content_box, block);
		
		var buttons_box = lz.el.div("system_buttons");
		for (var key in buttons)
		{
			var button = lz.el.button(buttons[key].name, buttons[key].class_name);
			lz.el.append(button, buttons_box);
			if (buttons[key].name == "close")
			{
				lz.el.click(button, self.system_close, self, block);
				lz.el.keypress(button, self.system_close, self, block);
			}
		}
		
		lz.el.append(buttons_box, block);

		return block;
	}
	
	self.open = function(block)
	{
		var back = lz.el.div("blocks_back");
		lz.el.click(back, self.system_close, self, block);
		back.style.zIndex = self.z_index;
		self.z_index += 2;
		
		lz.el.append(back, lz.body());
		lz.el.append(block, lz.body());

		var window_height = document.documentElement.clientHeight;
		if (block.offsetHeight + 100 > window_height)
		{
			block.style.height = (window_height - 100) + "px";

			var children = block.childNodes;
			var delta = children[1].offsetHeight - children[1].clientHeight + ((children[1].scrollWidth > children[1].clientWidth) ? 23 : 0);
			children[1].style.height = (block.clientHeight - 4 - (children[0].offsetHeight + children[2].offsetHeight) - delta) + "px";
		}
		
		var top = Math.round(block.offsetHeight / 2) * (-1) - 20;
		var left = Math.round(block.offsetWidth / 2) * (-1);

		block.style.marginTop = top + "px";
		block.style.marginLeft = left + "px";
		
		lz.by_class("submit", block)[0].focus();
	}
	
	self.system_close = function(event, object)
	{
		if (event.type == "click" || (event.type == "keypress" && event.charCode == 32))
		{
			lz.el.remove(object.previousSibling);

			if (object.previousSibling && object.previousSibling.className == "system_block")
			{
				var button = lz.by_class("submit", object.previousSibling)[0];
				if (button)
				{
					button.focus();
				}
			}
			lz.el.remove(object);
			
			self.z_index -= 2;
		}
	}
	
	self.add = function(event, block, unit, id)
	{
		id = id || false;
		
		var mas = {};
		mas.block = block;
		mas.unit = unit;
		
		if (id)
		{
			mas.edit = id
		}
		
		lz.hash.add(mas);
	}
	
	self.get_block = function(block, unit)
	{
		var this_block = self.isset_block(block);

		if (this_block)
		{
			self.load(this_block);
		}
		else
		{
			
			var data = {"block": block,
						"unit": unit,
						"tree": self.tree};
						
			lz.ajax.add("blocks", "get", data, self.init_block);
				
			lz.wait();
			lz.ajax.send();
		}
	}
	
	self.isset_block = function(block)
	{
		for (var key in self.list)
		{
			if (self.list[key].block == block)
			{
				return self.list[key];
			}
		}
		
		return false;
	}
	
	self.init_block = function(data)
	{
		if (data.tree)
		{
			self.tree = data.tree;
		}
		
		lz.fields.list[data.blocks_id] = {};
		lz.fields.list[data.blocks_id].fields_config = data.fields_config;
		
		lz.debug.errors = lz.extend(lz.debug.errors, data.errors);
		lz.debug.hints = lz.extend(lz.debug.hints, data.hints);
		lz._lazy._langs = lz.extend(lz._lazy._langs, data.langs);

		var div = lz.el.div();
		lz.el.html(data.page, div);
		self.list[data.blocks_id] = {};
		self.list[data.blocks_id].blocks_id = data.blocks_id;
		self.list[data.blocks_id].object = div.childNodes[0];
		
		var close_button = lz.el.a("", "close_block");
		lz.el.click(close_button,  self.close, self);
		lz.el.append(close_button, self.list[data.blocks_id].object);
		
		self.files_count = lz.array.count(data.js) + lz.array.count(data.css);
		
		for (var key in self.list)
		{
			if (self.list[key].add_block && self.list[key].add_block == data.block && self.list[key].items_list)
			{
				self.list[data.blocks_id].edit_data = {};
				for (var k in self.list[key].items_list)
				{
					self.list[data.blocks_id].edit_data[self.list[key].items_list[k].id] = self.list[key].items_list[k]; 
				}
			}
			
		}
		
		self.include_js(data);
		self.include_css(data);
		self.load(self.list[data.blocks_id]);
	}
	
	self.init_edit = function(data)
	{
		if (data.edit_data)
		{
			self.list[data.blocks_id].edit_data = lz.extend(self.list[data.blocks_id].edit_data, data.edit_data);
		}
		
		self.load(self.list[data.blocks_id]);
	}
	
	self.include_js = function(block)
	{
		var head = document.getElementsByTagName('head')[0];
		for (var key in block.js)
		{
			var script = document.createElement('script');
			script.setAttribute('src', block.js[key]);
			script.setAttribute('type', 'text/javascript');
			script.onload = function() {
				self.files_count--;
				self.load(block);
			};
			head.appendChild(script);
		}
	}
	
	self.include_css = function(block)
	{
		var head = document.getElementsByTagName('head')[0];
		
		for (var key in block.css)
		{
			var css = document.createElement('link');
			css.setAttribute('href', block.css[key]);
			css.setAttribute('rel', 'stylesheet');
			css.setAttribute('media', 'screen');
			css.setAttribute('type', 'text/css');
			css.onload = function() {
				self.files_count--;
				self.load(block);
			};
			head.appendChild(css);
		}
	}
	
	self.load = function(block)
	{
		if (self.files_count == 0)
		{
			var options = lz.hash.get(block.unit, block.block);			
			self.list[block.blocks_id].edit = false;	
			for (key in options)
			{
				self.list[block.blocks_id][key] = options[key];
			}
			
			if ( ! block.changed_lang)
			{
				self.set_edit(self.list[block.blocks_id]);
			}
			
			block.changed_lang = false;
			
			if (self.list[block.blocks_id].for_langs)
			{
				self.set_langs_box(self.list[block.blocks_id]);
				self.set_lang(self.list[block.blocks_id]);
			}
			
			lz.hide();
			self.display_block(self.list[block.blocks_id]);
		}
	}
	
	self.set_lang = function(block)
	{
		for (var i = 0, count = block.fields.length; i < count; i++)
		{
			if (block.fields[i].langs == "admin" || block.fields[i].langs == "client")
			{
				for (var j = 0, count_langs = lz.langs[block.fields[i].langs].length; j < count_langs; j++)
				{
					if (lz.langs[block.fields[i].langs][j].code != block.this_lang)
					{
						lz.el.remove_class(block.fields[i][lz.langs[block.fields[i].langs][j].code].object.parentNode.parentNode, "active");
					}
					else
					{
						lz.el.add_class(block.fields[i][lz.langs[block.fields[i].langs][j].code].object.parentNode.parentNode, "active");
					}
				}
			}
		}
		
	}
	
	self.set_langs_box = function(block)
	{
		if ((block.for_langs == "admin" || block.for_langs == "client") && ! block.langs_box)
		{
			for (var i = 0, count = lz.langs[block.for_langs].length; i < count; i++)
			{
				var link = lz.el.a();
				lz.el.click(link, self.change_lang, self, block, lz.langs[block.for_langs][i].code);
				link.innerHTML = lz.langs[block.for_langs][i].name;
				lz.el.before(link, block.object);
			}
			
			block.langs_box = true;
		}
	}
	
	self.display_block = function(block)
	{
		lz.el.empty(lz.page());
		lz.el.append(block.object, lz.page());
	}
	
	self.close = function()
	{
		lz.hash.remove();
	}
	
	self.set_edit = function(block)
	{
		if (block.edit && block.edit_data && block.edit_data[block.edit])
		{
			for (var i = 0, count = block.fields.length; i < count; i++)
			{
				if (block.edit_data[block.edit][block.fields[i].code])
				{	
					if (block.fields[i].langs == "admin" || block.fields[i].langs == "client")
					{
						for (var j = 0, count_langs = lz.langs[block.fields[i].langs].length; j < count_langs; j++)
						{
							lz.fields.set_value(block.fields[i][lz.langs[block.fields[i].langs][j].code].object, block.edit_data[block.edit][block.fields[i].code][lz.langs[block.fields[i].langs][j].code]);
							lz.debug.remove_hint(block.fields[i][lz.langs[block.fields[i].langs][j].code].object);
						}	
					}
					else
					{
						lz.fields.set_value(block.fields[i].object, block.edit_data[block.edit][block.fields[i].code]);
						lz.debug.remove_hint(block.fields[i].object);
					}
				}
			}
		}
		else
		{
			self.reset_fields(block);
		}
	}
	
	self.reset_fields = function(block)
	{
		for (var i = 0, count = block.fields.length; i < count; i++)
		{	
			if (block.fields[i].langs == "admin" || block.fields[i].langs == "client")
			{
				for (var j = 0, count_langs = lz.langs[block.fields[i].langs].length; j < count_langs; j++)
				{
					lz.fields.set_value(block.fields[i][lz.langs[block.fields[i].langs][j].code].object);
					lz.debug.remove_hint(block.fields[i][lz.langs[block.fields[i].langs][j].code].object);
				}	
			}
			else
			{
				lz.fields.set_value(block.fields[i].object);
				lz.debug.remove_hint(block.fields[i].object);
			}
		}
	}
	
	self.change_lang = function(event, block, lang)
	{
		if (block.this_lang != lang)
		{
			block.changed_lang = true;
			lz.hash.edit(block.unit, block.block, {"this_lang": lang});
		}
	}
}