function pages_print(items)
{
	var box = document.createElement("div");
	box.className = "row_box " + items.style;
	box.setAttribute("onmouseover", "_show.over(this)");
	box.setAttribute("onmouseout", "_show.out(this)");
	box.setAttribute("ondblclick", "_lazy_hash_add(\"pages_add\", \"pages\", \"" + _locals.default("client") + "\", \"id:" + items['id'] + "\")");
	
	var table = document.createElement("table");
	table.className = "table_hundret";
	
	var tr = document.createElement("tr");
	tr.className = "tr_result";
	
	var td_group = document.createElement("td");
	td_group.className = "td_group";
	
	var input_group = document.createElement("input");
	input_group.className = "group";
	input_group.type = "checkbox";
	input_group.checked = (items['group'] == "1") ? true : false;
	input_group.setAttribute("onchange", "_filter.group(this, \"" + items['id'] + "\")");
	
	td_group.appendChild(input_group);
	tr.appendChild(td_group);
	
	var td_id = document.createElement("td");
	td_id.className = "td_id";
	
	var p_id = document.createElement("p");
	p_id.className = "text";
	p_id.innerHTML = items['id'];
	
	td_id.appendChild(p_id);
	tr.appendChild(td_id);
	
	var td_name = document.createElement("td");
	td_name.className = "td_name";
	
	var p_name = document.createElement("p");
	p_name.className = "text";
	p_name.innerHTML = items['name'];
	
	td_name.appendChild(p_name);
	tr.appendChild(td_name);
	
	var td_active = document.createElement("td");
	td_active.className = "td_check";
	
	var input_active = document.createElement("input");
	input_active.className = "check";
	input_active.type = "checkbox";
	input_active.checked = (items['active'] == "1") ? true : false;
	input_active.setAttribute("onchange", "_show.check(this, \"pages\", \"pages_items\", \"active\", \"" + items['id'] + "\")");
	
	td_active.appendChild(input_active);
	tr.appendChild(td_active);
	
	var td_mark = document.createElement("td");
	td_mark.className = "td_check";
	
	var input_mark = document.createElement("input");
	input_mark.className = "check";
	input_mark.type = "checkbox";
	input_mark.checked = (items['mark'] == "1") ? true : false;
	input_mark.setAttribute("onchange", "_show.check(this, \"pages\", \"pages_items\", \"mark\", \"" + items['id'] + "\")");
	
	td_mark.appendChild(input_mark);
	tr.appendChild(td_mark);
	
	var td_edit = document.createElement("td");
	td_edit.className = "td_button";
	
	var a_edit = document.createElement("a");
	a_edit.className = "button button_show edit_item";
	a_edit.href = "javascript:void(0)";
	a_edit.setAttribute("onclick", "_lazy_hash_add(\"pages_add\", \"pages\", \"" + _locals.default("client") + "\", \"id:" + items['id'] + "\")");
	a_edit.setAttribute("data-tip", _locals.get("tooltip_edit_icons"));
	
	var top_edit = document.createElement("span");
	top_edit.className = "button_top";
	
	var content_edit = document.createElement("span");
	content_edit.className = "button_content";
	
	a_edit.appendChild(top_edit);
	a_edit.appendChild(content_edit);
	td_edit.appendChild(a_edit);
	tr.appendChild(td_edit);
	
	var td_delete = document.createElement("td");
	td_delete.className = "td_button";
	
	var a_delete = document.createElement("a");
	a_delete.className = "button button_show delete_item";
	a_delete.href = "javascript:void(0)";
	a_delete.setAttribute("onclick", "_lazy_block_delete(\"" + items['id'] + "\", \"pages\", \"pages_add\")");
	a_delete.setAttribute("data-tip", _locals.get("tooltip_delete_icons"));
	
	var top_delete = document.createElement("span");
	top_delete.className = "button_top";
	
	var content_delete = document.createElement("span");
	content_delete.className = "button_content";
	
	a_delete.appendChild(top_delete);
	a_delete.appendChild(content_delete);
	td_delete.appendChild(a_delete);
	tr.appendChild(td_delete);
	
	table.appendChild(tr);
	box.appendChild(table);
	
	return box;
}