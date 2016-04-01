lz.blocks.create("Units_show");
function Units_show()
{
	var self = this;
	self.add_block = "units_add";
	
	self.submit_callback = function(data)
	{

	}

	self.print = function()
	{
		var container = lz.el.div("show_block", self.id);
		var add_link = lz.el.a("", "add_block");
		lz.el.click(add_link, self.add, self, "units_add", "units");
		add_link.innerHTML = "add block";
		lz.el.append(add_link, container);

		var table = lz.el.table("table table_hundret");
		var tr_head = lz.el.tr("tr_head");
		
		var td_id = lz.el.td("td_id");
		var p_id = lz.el.p();
		var node_id = lz.el.text("id");
		
		lz.el.append(node_id, p_id);
		lz.el.append(p_id, td_id);
		lz.el.append(td_id, tr_head);
		
		var td_name = lz.el.td("td_name");
		var p_name = lz.el.p();
		var node_name = lz.el.text(lz._lazy._langs.units_name);

		lz.el.append(node_name, p_name);
		lz.el.append(p_name, td_name);
		lz.el.append(td_name, tr_head);
		
		var td_folder = lz.el.td("td_folder");
		var p_folder = lz.el.p();
		var node_folder = lz.el.text(lz._lazy._langs.units_folder);

		lz.el.append(node_folder, p_folder);
		lz.el.append(p_folder, td_folder);
		lz.el.append(td_folder, tr_head);
		
		var td_edit = lz.el.td("td_edit");
		var p_edit = lz.el.p();
		var node_edit = lz.el.text("edit");

		lz.el.append(node_edit, p_edit);
		lz.el.append(p_edit, td_edit);
		lz.el.append(td_edit, tr_head);
		
		lz.el.append(tr_head, table);

		for (var i = 0, count = self.items_list.length; i < count; i++)
		{
			var item = self.items_list[i];

			var tr = lz.el.tr();
			var td_id = lz.el.td("td_id");
			var p_id = lz.el.p();
			var node_id = lz.el.text(item['id']);

			lz.el.append(node_id, p_id);
			lz.el.append(p_id, td_id);
			lz.el.append(td_id, tr);
			
			var td_name = lz.el.td("td_name");
			var p_name = lz.el.p();
			var node_name = lz.el.text(item['items_name'][lz.langs._default.admin]);

			lz.el.append(node_name, p_name);
			lz.el.append(p_name, td_name);
			lz.el.append(td_name, tr);
			
			var td_folder = lz.el.td("td_folder");
			var p_folder = lz.el.p();
			var node_folder = lz.el.text(item['items_folder']);

			lz.el.append(node_folder, p_folder);
			lz.el.append(p_folder, td_folder);
			lz.el.append(td_folder, tr);
			
			var td_edit = lz.el.td("td_edit");
			var a_edit = lz.el.a("", "link_edit");
			lz.el.click(a_edit, self.add, self, "units_add", "units", item['id']);

			lz.el.append(a_edit, td_edit);
			lz.el.append(td_edit, tr);
			
			lz.el.append(tr, table);
		}
		
		lz.el.append(table, container);
		lz.el.empty(lz.page());
		lz.el.append(container, lz.page());
	}
}