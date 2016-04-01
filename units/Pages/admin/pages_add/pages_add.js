function pages_add_submit(mas, callback)
{
	callback = callback || "_lazy_block_submit_edit";
	_errors.add('pages_text');
	_ajax.add('pages', 'save', mas, callback, 'pages_add');
	_filter.send_filter(true);
}