<?php

	class Pages extends Unit
	{
		var $_unit = "";
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}

		function output($info, $params = array())
		{
			/*switch ($info['folder'])
			{
				case "pages_show": System::$lazy['result'] = $this->get(); break;
			}*/
			
			return $this->files->block($info);
		}

		function save($post)
		{
			mt_srand();
			$post['pages_text_code'] = ($post['pages_text_code'] == "") ? time().mt_rand(1000, 9999).".php" : $post['pages_text_code'];
			
			$data_array = array("pages_active" => $post['pages_active'],
								"pages_mark" => $post['pages_mark'],
								"pages_text" => $post['pages_text_code']);

			$_values['table'] = 'pages_items';
			$items_id = $post['id'];
			if ( ! $items_id || $items_id == "0")
			{
				$data_array['pages_add'] = time();
				
				if ($this->db->insert("pages_items", $data_array))
				{
					$items_id = $this->db->insert_id();
					$this->debug->log("insert_ok", OK);
				}
				else
				{
					$this->debug->log("insert", ERROR, $_values);
				}
			}
			else
			{
				$data_array['pages_edit'] = time();
				$this->db->where('pages_id', $items_id);
				if ($this->db->update("pages_items", $data_array))
				{
					$this->debug->log("update_ok", OK);
				}
				else
				{
					$this->debug->log("update", ERROR, $_values);
				}
				
			}

			$langs_array = array("items_name" => $post['pages_name']);
			$this->locals->save($this->unit, $items_id, 'items', $langs_array);
			$this->files->write($this->unit, $items_id, 'items', $post['pages_text'], $post['pages_text_code']);
			$this->aliases->save($this->unit, $items_id, 'items', $post['aliases_pages_name']);
			$files = $this->uploads->save($this->unit, $items_id, 'items', $post['files'], (isset(System::$lazy['_config']['pages_add']['cfg']['formats'])) ? System::$lazy['_config']['pages_add']['cfg']['formats'] : array());
			
			return array('fields' => array('pages_text_code' => $post['pages_text_code']),
						 'id' => $items_id,
						 'next_id' => $this->bases->prev_langs_id($this->unit, $_values['table'], 'pages_id', $items_id, 'items_name'),
						 'prev_id' => $this->bases->prev_langs_id($this->unit, $_values['table'], 'pages_id', $items_id, 'items_name'),
						 'files' => $files);
		}
		
		function filter_convert($filter_array)
		{
			$filter = array("system" => array(),
							"unit" => array());
							
			$filter_keys = array("sort_field", "sort_dir", "this_page", "on_page", "lang");
			foreach ($filter_array as $key => $val)
			{
				if (in_array($key, $filter_keys))
				{
					$filter['system'][$key] = $val;
				}
				else
				{
					$filter['unit'][$key] = $val;
				}
			}
			
			return $filter;
		}
		
		function ids($filter)
		{
			$like_items = array();
			$like_texts = array();
			foreach ($filter['unit'] as $key => $val)
			{
				if (strpos($key, ".") === FALSE)
				{
					$like_items[$key] = $val;
				}
				else
				{
					$part = explode(".", $key);
					$like_texts[$part[0]][$part[1]] = $val;
				}
			}
			
			$ids = array();
			if (count($like_items) > 0)
			{
				$this->db->select("pages_id");
				foreach ($like_items as $key => $val)
				{
					$this->db->like($key, $val);
				}
				
				$_values['table'] = "pages_items";
				if ($result = $this->db->get($_values['table']))
				{
					if ($result->num_rows() > 0)
					{
						foreach ($result->result_array() as $row)
						{
							$ids[] = $row['pages_id'];
						}
					}
					else
					{
						$this->debug->log("empty", ERROR, $_values);
					}
				}
				else
				{
					$this->debug->log("get", ERROR, $_values);
				}
			}
			
			if (count($like_texts) > 0)
			{
				$langs_code = isset($filter['system']['lang']) ? $filter['system']['lang'] : Locals::$langs['default']['client'];
				foreach ($like_texts as $table => $value)
				{
					$this->db->select("items_id");
					foreach ($value as $key => $val)
					{
						$this->db->like($key, $val);
					}
					$this->db->where("items_table", "items");
					$this->db->where("langs_code", $langs_code);
					
					$_values['table'] = "pages_texts";
					if ($result = $this->db->get($_values['table']))
					{
						if ($result->num_rows() > 0)
						{
							foreach ($result->result_array() as $row)
							{
								$ids[] = $row['items_id'];
							}
						}
						else
						{
							$this->debug->log("empty", ERROR, $_values);
						}
					}
					else
					{
						$this->debug->log("get", ERROR, $_values);
					}
				}
			}
			
			return $ids;
		}
		
		function get($post = array())
		{
			$filter = $this->filter_convert($post);
			$ids = $this->ids($filter);
			$count = count($ids);
			$items['data'] = array();

			$this_page = isset($filter['system']['this_page']) ? $filter['system']['this_page'] : 1;
			System::$lazy['_config']['pages_show']['cfg']['filter']['this_page'] = $this_page;
			
			$items['all'] = $this->db->count_all_results("pages_items");
			
			if (count($filter['unit']) > 0 && $count > 0 || count($filter['unit']) == 0)
			{
				$items['rows'] = ($count > 0) ? $count : $items['all'];
				$on_page = (isset($filter['system']['on_page'])) ? $filter['system']['on_page'] : ((isset(System::$lazy['_config']['pages_show']['cfg']['filter']['on_page'])) ? System::$lazy['_config']['pages_show']['cfg']['filter']['on_page'] : 20);	
				$items['pages'] = ceil($items['rows'] / $on_page);
				
				$sort_field = isset($filter['system']['sort_field']) ? $filter['system']['sort_field'] : ((isset(System::$lazy['_config']['pages_show']['cfg']['filter']['sort_field'])) ? System::$lazy['_config']['pages_show']['cfg']['filter']['sort_field'] : "pages_id");
				$sort_dir = isset($filter['system']['sort_dir']) ? $filter['system']['sort_dir'] : ((isset(System::$lazy['_config']['pages_show']['cfg']['filter']['sort_dir'])) ? System::$lazy['_config']['pages_show']['cfg']['filter']['sort_dir'] : "asc");
				$this->db->order_by($sort_field, $sort_dir);
				
				$select = array($this->unit."_id as id",
								$this->unit."_active as active",
								$this->unit."_mark as mark",
								$this->unit."_texts.items_name as name");
				$this->db->select(implode(", ", $select));
				
				if ($count > 0)
				{
					$this->db->where_in("pages_id", $ids);
				}
				
				$this->db->join("pages_texts", "pages_items.pages_id = pages_texts.items_id");
				$this->db->where("pages_texts.items_table", "items");
				$this->db->where("pages_texts.langs_code", isset($filter['system']['lang']) ? $filter['system']['lang'] : Locals::$langs['default']['client']);
				$this->db->limit($on_page, ($this_page - 1) * $on_page);
				
				$_values['table'] = "pages_items";
				if ($result = $this->db->get($_values['table']))
				{
					if ($result->num_rows() > 0)
					{
						$items['data'] = array();
						foreach ($result->result_array() as $row)
						{
							$items['data'][] = $row;
						}
					}
					else
					{
						$this->debug->log("empty", ERROR, $_values);
					}
				}
				else
				{
					$this->debug->log("get", ERROR, $_values);
				}
			}

			return $items;
		}

		function edit($post)
		{
			$_values['table'] = "pages_items";
			$this->db->select("pages_text, pages_active, pages_mark");
			$this->db->where('pages_id', $post['id']);
			$this->db->limit(1);
			
			if ($result = $this->db->get($_values['table']))
			{
				if ($result->num_rows() > 0)
				{
					$row = $post;
					$row['data'] = $result->row_array();
					$row['data']['pages_text_code'] = $row['data']['pages_text'];
					
					if ($langs = $this->locals->edit($this->unit, $post['id'], 'items', array('items_name')))
					{
						$row['data'] = array_merge($row['data'], $langs);
					}
					
					$row['data'] = array_merge($row['data'], $this->files->edit($this->unit, $post['id'], 'items', $row['data']['pages_text_code'], 'pages_text'));
					$row['data'] = array_merge($row['data'], $this->aliases->edit($this->unit, $post['id'], 'items', 'pages_name'));
					$row['data']['_files']['uploads_block'] = $this->uploads->files($this->unit, $post['id'], 'uploads_block', 'preview');
					
					$row['next_id'] = $this->bases->next_langs_id($this->unit, $_values['table'], 'pages_id', $post['id'], 'items_name');
					$row['prev_id'] = $this->bases->prev_langs_id($this->unit, $_values['table'], 'pages_id', $post['id'], 'items_name');
					
					$this->debug->log("edit_ok", OK);
					return $row;
				}
				else
				{
					return $this->debug->log("empty", ERROR, $_values);
				}
			}
			else
			{
				return $this->debug->log("get", ERROR, $_values);
			}
			
			return $post;
		}

		function delete($post)
		{
			if ( ! is_array($post['id']))
			{
				$post['id'] = array($post['id']);
			}
			
			$item = $post;
			$item['table'] = 'items';
			
			$this->db->where_in("pages_id", $post['id']);
			if ($this->db->delete("pages_items"))
			{
				for ($i = 0; $i < count($post['id']); $i++)
				{
					$item['id'] = $post['id'][$i];
					$this->locals->delete($item);
					$this->uploads->delete_item($item);
					$this->files->delete($item);
			 		$this->aliases->delete($item);
				}
				
				$this->debug->log("delete_ok", OK);
				return $post;
			}
			else 
			{
				return $this->debug->log("delete", ERROR);
			}
		}

		function actions($post)
		{
			$item = array('prefix' => $post['unit'],
						  'table' => 'pages_items',
						  'field' => $post['field'],
						  'check' => $post['check'],
						  'id' => $post['id']);
						  
			return $this->bases->check($item);
		}
	}