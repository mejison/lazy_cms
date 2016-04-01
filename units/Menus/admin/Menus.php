<?php

	class Menus extends Unit
	{
		var $_unit = "";
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($info)
		{
			if ($info['folder'] != $this->_unit."_content")
			{
				if ($info['folder'] == $this->_unit."_title")
				{
					System::$lazy[$this->_unit.'_title'] = $this->title(System::$lazy['_this']);
				}
				
				if ($info['folder'] == $this->_unit."_menu")
				{
					System::$lazy[$this->_unit.'_menu'] = $this->menu();
				}
				
				if ($info['folder'] == "menus_add")
				{
					System::$lazy['menus_blocks'] = $this->get_blocks();
					System::$lazy['units_list'] = $this->get_units();
				}
			}
			else
			{
				System::$lazy['this_block'] = System::$lazy['_this']['info']['folder'];
				System::$lazy['_blocks_id'] = System::$lazy['_this']['info']['blocks_id'];

				$unit = System::$lazy['_this']['unit'];
				$this->load->unit($unit);
				System::$lazy['_content'] = $this->$unit->output(System::$lazy['_this']['info']);
			}
			
			return $this->files->block($info, $this->unit);
		}
		
		function menu()
		{
			$_values['table'] = "units_cats";
			$this->db->select("cats_id as id, cats_name as name");
			$this->db->where("cats_active", TRUE);
			$this->db->order_by("cats_pos", "asc");
			$result = $this->db->get_lang($_values['table'], Langs::$_config['this']);
			
			if (count($result) > 0)
			{
				$items = array();
				foreach ($result as $row)
				{
					$_values['table'] = "units_items";
					$this->db->select("items_name as name, items_folder as folder, items_default as def");
					$this->db->where("cats_id", $row['id']);
					$this->db->where("items_active", TRUE);
					$this->db->order_by("items_pos", "asc");
					$res = $this->db->get_lang($_values['table'], Langs::$_config['this']);
					
					if (count($res) > 0)
					{
						$units = array();
						foreach ($res as $val)
						{
							$_values['table'] = $val['folder']."_blocks";
							if ($this->db->table_exists($_values['table']))
							{
								$this->db->where("blocks_type", TYPE);
								$this->db->where("blocks_active", TRUE);
								$this->db->where("blocks_default", TRUE);
								$this->db->where("blocks_level >=", Access::$user['level']);
								$this->db->where("blocks_logged_in >=", 1);
								$this->db->limit(1);
								if ($count = $this->db->count_all($_values['table']))
								{
									$units[] = $val;
								}
							}
						}

						if (count($units) > 0)
						{
							$row['units'] = $units;
							$items[] = $row; 
						}
					}
				}

				if (count($items) > 0)
				{
					$this->debug->add("units_groups_ok", OK);
					return $items;
				}
				else
				{
					return $this->debug->add("units_groups_empty", ERROR); 
				}
			}
			else
			{
				return $this->debug->add("units_groups_empty", ERROR);
			}
		
		}
		
		function title($_this)
		{
			/*$_values = array();
			if ($_this['cat'] != 0)
			{
				$this->db->select($_this['unit']."_texts.items_name as name");
				$this->db->where($_this['unit']."_cats.cats_id", $_this['cat']);
				$this->db->where($_this['unit']."_texts.langs_code", Langs::$_config['this']);
				$this->db->where($_this['unit']."_texts.items_table", "cats");
				$this->db->join($_this['unit']."_texts", $_this['unit']."_cats.cats_id = ".$_this['unit']."_texts.items_id");
				$this->db->limit(1);
				
				$_values['table'] = $_this['unit']."_cats";
				$result = $this->db->get($_values['table']);
			}
			else
			{
				if ($_this['item'] == 0)
				{
					$this->db->select($_this['unit']."_texts.items_name as name");
					$this->db->where($_this['unit']."_blocks.blocks_id", $_this['block']);
					$this->db->where($_this['unit']."_texts.langs_code", Langs::$_config['this']);
					$this->db->where($_this['unit']."_texts.items_table", "blocks");
					$this->db->join($_this['unit']."_texts", $_this['unit']."_blocks.blocks_id = ".$_this['unit']."_texts.items_id");
					$this->db->limit(1);

					$_values['table'] = $_this['unit']."_blocks";					
					$result = $this->db->get($_values['table']);
				}
				else
				{
					$this->db->select($_this['unit']."_texts.items_name as name");
					$this->db->where($_this['unit']."_items.".$_this['unit']."_id", $_this['id']);
					$this->db->where($_this['unit']."_texts.langs_code", Langs::$_config['this']);
					$this->db->where($_this['unit']."_texts.items_table", "items");
					$this->db->join($_this['unit']."_texts", $_this['unit']."_items.items_id = ".$_this['unit']."_texts.items_id");
					$this->db->limit(1);
					
					$_values['table'] = $_this['unit']."_items";
					$result = $this->db->get($_values['table']);
				}
			}
			
			if ($result)
			{
				if ($result->num_rows() > 0)
				{
					$this->debug->add("title_ok", OK);
					
					$row = $result->row_array();
					return $row['name'];
				}
				else
				{
					return $this->debug->add("empty", ERROR, $_values);
				}
			}
			else
			{
				return $this->debug->add("get", ERROR, $_values);
			}*/
			return "Title";
		}
		
		function get_blocks()
		{
			$this->db->select("blocks_id");
			$this->db->where("blocks_type", "client");
			$this->db->where("url_type", 2);
			
			$blocks = array();
			$_values['table'] = "menus_blocks";
			if ($result = $this->db->get($_values['table']))
			{
				if ($result->num_rows() > 0)
				{
					foreach ($result->result_array() as $row)
					{
						$blocks[$row['blocks_id']] = $this->locals->text($this->unit, $row['blocks_id'], "blocks", "items_name");
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
			
			return $blocks;
		}
		
		function get_units()
		{
			$this->db->select("units_id, units_folder");
			$this->db->where("units_active", TRUE);
			
			$items = array();
			$_values['table'] = "units_items";
			if ($result = $this->db->get($_values['table']))
			{
				if ($result->num_rows() > 0)
				{
					foreach ($result->result_array() as $row)
					{
						$items[$row['units_folder']] = $this->locals->text("units", $row['units_id'], "items", "items_name");
						$items[$row['units_folder']] = ($items[$row['units_folder']] == "") ? $row['units_folder'] : $items[$row['units_folder']];
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
			
			return $items;
		}
		
		function source_get($post)
		{
			$items = array("blocks" => array(),
						   "items" => array());
			
			$this->db->order_by("blocks_pos", "asc");
			$this->db->select("blocks_id, blocks_folder");
			$this->db->where("blocks_type", "client");
			$this->db->where("blocks_active", TRUE);
			$this->db->where("url_type", 2);
			
			$_values['table'] = $post['folder']."_blocks";
			if ($result = $this->db->get($_values['table']))
			{
				if ($result->num_rows() > 0)
				{
					foreach ($result->result_array() as $row)
					{
						$items['blocks'][$row['blocks_id']] = $this->locals->text($post['folder'], $row['blocks_id'], "blocks", "items_name");
						$items['blocks'][$row['blocks_id']] = ($items['blocks'][$row['blocks_id']] == "") ? $row['blocks_folder'] : $items['blocks'][$row['blocks_id']];
					}
				}
				else
				{
					$this->debug->log("get", ERROR, $_values);
				}
			}
			else
			{
				$this->debug->log("get", ERROR, $_values);
			}
			
			return $items;
		}
		
		 /*
		function block_items($lazy_config, $blocks_info, $parents_id = 0)
		{
			$select_mas = array($this->unit."_id as id",
								"blocks_id",
								"items_id",
								"items_blocks_id",
								"items_unit",
								$this->unit."_name as name",
								$this->unit."_title as title",
								$this->unit."_link as link",
								$this->unit."_default as def",
								$this->unit."_blank as blank",
								$this->unit."_page as page",
								$this->unit."_classes as classes",
								$this->unit."_logged_in as logged_in");
			
			$this->db->select(implode(", ", $select_mas));
			$this->db->order_by($this->unit."_pos", "asc");
			$this->db->where("parents_id", $parents_id);
			$this->db->where("blocks_id", $blocks_info['blocks_id']);
			
			if ($result = $this->db->get($this->unit."_blocks_items"))
			{
				if ($result->num_rows() > 0)
				{
					$items = array();
					$by_unit = array();
					$this_url = ($this->uri->uri_string() == "") ? "/" : "/".$this->uri->uri_string()."/";
					foreach ($result->result_array() as $row)
					{
						if ($row['items_unit'] != "")
						{
							if ($row['items_id'] != 0)
							{
								$lazy_config = $this->bases_add->fields($lazy_config, $row['items_id'], $row['items_unit']."_name", $row['items_unit']."_items");
								if ($lazy_config['lazy_check'])
								{
									$row['items_name'] = $this->langs->text($lazy_config, $lazy_config['lazy_temp']);
								}
							}
							else
							{
								$lazy_config = $this->bases_add->fields($lazy_config, $row['items_blocks_id'], "blocks_name", $row['items_unit']."_blocks");
								if ($lazy_config['lazy_check'])
								{
									$row['items_name'] = $this->langs->text($lazy_config, $lazy_config['lazy_temp']);
								}
							}
						}
						
						$row['name'] = $this->langs->text($lazy_config, $row['name']);
						$row['title'] = $this->langs->text($lazy_config, $row['title']);
						$row['link'] = $this->langs->text($lazy_config, $row['link']);
						
						$row['url'] = "";
						$lazy_config = $this->url_get($lazy_config, $row);
						if ($lazy_config['lazy_check'])
						{
							$row['url'] = $lazy_config['lazy_temp'];
						}

						$row['this'] = ($this_url == $row['url']) ? TRUE : FALSE;
						
						$row['sub'] = array();
						$lazy_config = $this->block_items($lazy_config, $blocks_info, $row['id']);
						if ($lazy_config['lazy_check'])
						{
							$row['sub'] = $lazy_config['lazy_temp'];
						}
						
						$items[] = $row;
					}
					
					$lazy_config = $this->errors->add($lazy_config, "block_items_ok", TRUE, array(), $items);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "block_items_get", FALSE, array(), array());
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "block_items_get", FALSE, array(), array());
			}
			
			return $lazy_config;
		}
		
		function url_get($lazy_config, $row, $lang = "")
		{
			$lang = ($lang == "") ? $lazy_config['langs_this'] : $lang;
			$url = "";
			if ($row['page'])
			{
				if ($row['def'])
				{
					$url = ($lang == $lazy_config['langs_'.$lazy_config['lazy_type'].'_default']) ? "/" : "/".$lang."/";
				}
				else
				{
					if ($row['link'] != "")
					{
						$url = $row['link'];
					}
					else
					{
						if ($row['items_unit'] != "")
						{
							if ($row['items_id'] == 0)
							{
								$lazy_config = $this->aliases_add->get($lazy_config, 0, $row['items_unit'], $row['items_blocks_id'], $lang);
								if ($lazy_config['lazy_check'])
								{
									$aliases = $lazy_config['lazy_temp'];
									$lazy_config = $this->blocks_add->url_type($lazy_config, $row['items_blocks_id'], $row['items_unit']);
									if ($lazy_config['lazy_check'])
									{
										switch($lazy_config['lazy_temp'])
										{
											case 1: $url = "/".$lang."/".$aliases."/"; break;
											case 2: $url = "/".$lang."/".$aliases."/0/"; break;
											case 3: $url = "/".$lang."/".$aliases."/0/1/"; break;
											default: $url = "/".$lang."/"; break;
										}
									}
								}
							}
							else
							{
								$lazy_config = $this->aliases_add->get($lazy_config, $row['items_id'], $row['items_unit'], 0, $lang);
								if ($lazy_config['lazy_check'])
								{
									$url = "/".$lang."/".$lazy_config['lazy_temp']."/";
								}
							}
						}
					}
				}
			}
			
			$lazy_config = $this->errors->add($lazy_config, "url_ok", TRUE, array(), $url);
			return $lazy_config;
		}
		
		

		*/ 	
	}
	
/* End of file menus_admin.php */