<?php

	class Admins extends Unit
	{
		var $_unit = "";
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($info, $params = array())
		{
			return $this->files->block($info);
		}
		
		function login($data)
		{
			$select_mas = array("items_id as id",
								"items_login as login",
								$this->_unit."_items.cats_id",
								$this->_unit."_cats.cats_level",
								$this->_unit."_cats.cats_create",
								$this->_unit."_cats.cats_edit",
								$this->_unit."_cats.cats_remove",
								$this->_unit."_cats.cats_pub",
								$this->_unit."_cats_texts.cats_name");
			$this->db->select(implode(", ", $select_mas));
			$this->db->where("items_login", strtolower($data['items_login']));
			$this->db->where("items_pass", md5($data['items_pass']));
			$this->db->where("items_active", TRUE);
			$this->db->where("cats_active", TRUE);
			$this->db->where("cats_delete", FALSE);
			$this->db->order_by_field($this->_unit.'_cats_texts.langs_code' , array(Langs::$_config['this'], Langs::$_config['default'][TYPE], LANG), 'ASC');
			$this->db->join($this->_unit."_cats", $this->_unit."_items.cats_id = ".$this->_unit."_cats.cats_id");
			$this->db->join($this->_unit.'_cats_texts', $this->_unit.'_cats_texts.cats_id = '.$this->_unit.'_cats.cats_id');
			$this->db->limit(1);
			
			if ($result = $this->db->get($this->_unit."_items"))
			{
				$sessions_array = array(TYPE."_logged_in" => TRUE,
										"id" => $result['id'],
										"login" => $data['items_login'],
										"cats_id" => $result['cats_id'],
										"cat" => $result['cats_name'],
										"level" => $result['cats_level'],
										"create" => $result['cats_create'],
										"edit" => $result['cats_edit'],
										"remove" => $result['cats_remove'],
										"pub" => $result['cats_pub']);

				$this->sessions->set($sessions_array);
				return $this->debug->add("login_ok");
			}
			else
			{
				return $this->debug->add("login_empty", ERROR);
			}
		}
		
		function logout($data)
		{
			$sessions_array = array(TYPE."_logged_in", "id", "login", "cats_id", "cat", "level", "create", "edit", "remove", "pub");
			$this->sessions->remove($sessions_array);
			
			return $this->debug->add("logout_ok");
		}
		



		
		function get_admins_access_blocks_model($lazy_config, $items)
		{
			$lazy_config = $this->langs->add_langs_file_model($lazy_config, "admins");
			$lazy_config = $this->errors->add_errors_file_model($lazy_config, "admins");
			
			$lazy_config = $this->get_admins_access_arrays_model($lazy_config);
			if ($lazy_config['lazy_check'])
			{
				if (count($lazy_config['items_only']) > 0)
				{
					$lazy_config = $this->get_only_access_blocks_model($lazy_config, $items, $lazy_config['items_only']);
				}
				else
				{
					if (count($lazy_config['items_except']) > 0)
					{
						$lazy_config = $this->get_except_access_blocks_model($lazy_config, $items, $lazy_config['items_except']);
					}
					else
					{
						$lazy_config = $this->get_all_access_blocks_model($lazy_config, $items);
					}
				}
			}

			return $lazy_config;
		}
		
		
		
		function get_only_access_blocks_model($lazy_config, $items)
		{
			$lazy_config = $this->langs->add_langs_file_model($lazy_config, "admins");
			$lazy_config = $this->errors->add_errors_file_model($lazy_config, "admins");
			
			$items_menus = array();
			foreach ($items as $row_groups)
			{
				if (count($row_groups['groups_units']) > 0)
				{
					$units_in_groups = array();
					foreach ($row_groups['groups_units'] as $row_units)
					{
						if (count($row_units['units_blocks']) > 0)
						{
							$blocks_in_units = array();
							foreach ($row_units['units_blocks'] as $row_blocks)
							{
								$blocks_access = FALSE;
								foreach ($lazy_config['items_only'] as $row_access)
								{
									if ($row_blocks['blocks_id'] == $row_access['blocks_id'] && $row_units['units_folder'] == $row_access['blocks_unit'])
									{
										$blocks_access = TRUE;
									}
								}
								
								if ($blocks_access)
								{
									$lazy_config = $this->menus->get_blocks_url_model($lazy_config, $row_units['units_folder'], $row_blocks);
									if ($lazy_config['lazy_check'])
									{
										$row_blocks['blocks_url'] = $lazy_config['lazy_temp'];
										$blocks_in_units[] = $row_blocks;
									}
								}
							}
							
							if (count($blocks_in_units) > 0)
							{
								$row_units['units_blocks'] = $blocks_in_units;
								$row_units['units_url'] = base_url().$lazy_config['admins_folder']."/".$lazy_config['langs_this']."/".$row_units['units_folder']."/";
								$lazy_config = $this->check_current_block_access_model($lazy_config, $row_units['units_folder']);
								if ( ! $lazy_config['lazy_check'])
								{
									$row_units['units_url'] = $row_units['units_blocks'][0]['blocks_url'];
								}
								
								$units_in_groups[] = $row_units;
							}
						}
					}
					
					if (count($units_in_groups) > 0)
					{
						$row_groups['groups_units'] = $units_in_groups;
						$row_groups['groups_url'] = "";
						$items_menus[] = $row_groups;
					}
				}
			}
			
			if (count($items_menus) > 0)
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_access_menus_ok", TRUE, array(), $items_menus);
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_access_menus_empty", FALSE);
			}
			
			return $lazy_config;
		}
		
		function get_except_access_blocks_model($lazy_config, $items, $items_except)
		{
			$lazy_config = $this->langs->add_langs_file_model($lazy_config, "admins");
			$lazy_config = $this->errors->add_errors_file_model($lazy_config, "admins");
			
			$items_menus = array();
			foreach ($items as $row_groups)
			{
				if (count($row_groups['groups_units']) > 0)
				{
					$units_in_groups = array();
					foreach ($row_groups['groups_units'] as $row_units)
					{
						if (count($row_units['units_blocks']) > 0)
						{
							$blocks_in_units = array();
							foreach ($row_units['units_blocks'] as $row_blocks)
							{
								$blocks_access = TRUE;
								foreach ($lazy_config['items_except'] as $row_access)
								{
									if ($row_blocks['blocks_id'] == $row_access['blocks_id'] && $row_units['units_folder'] == $row_access['blocks_unit'])
									{
										$blocks_access = FALSE;
									}
								}
								if ($blocks_access)
								{
									$lazy_config = $this->menus->get_blocks_url_model($lazy_config, $row_units['units_folder'], $row_blocks);
									if ($lazy_config['lazy_check'])
									{
										$row_blocks['blocks_url'] = $lazy_config['lazy_temp'];
										$blocks_in_units[] = $row_blocks;
									}
								}
							}
							
							if (count($blocks_in_units) > 0)
							{
								$row_units['units_blocks'] = $blocks_in_units;
								$row_units['units_url'] = base_url().$lazy_config['admins_folder']."/".$lazy_config['langs_this']."/".$row_units['units_folder']."/";
								$lazy_config = $this->check_current_block_access_model($lazy_config, $row_units['units_folder']);
								if ( ! $lazy_config['lazy_check'])
								{
									$row_units['units_url'] = $row_units['units_blocks'][0]['blocks_url'];
								}
								
								$units_in_groups[] = $row_units;
							}
						}
					}
					
					if (count($units_in_groups) > 0)
					{
						$row_groups['groups_units'] = $units_in_groups;
						$row_groups['groups_url'] = "";
						$items_menus[] = $row_groups;
					}
				}
			}
			
			if (count($items_menus) > 0)
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_access_menus_ok", TRUE, array(), $items_menus);
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_access_menus_empty", FALSE);
			}
			
			return $lazy_config;
		}
		
		function get_all_access_blocks_model($lazy_config, $items)
		{
			$lazy_config = $this->langs->add_langs_file_model($lazy_config, "admins");
			$lazy_config = $this->errors->add_errors_file_model($lazy_config, "admins");
			
			$items_menus = array();
			foreach ($items as $row_groups)
			{
				if (count($row_groups['groups_units']) > 0)
				{
					$units_in_groups = array();
					foreach ($row_groups['groups_units'] as $row_units)
					{
						if (count($row_units['units_blocks']) > 0)
						{
							$blocks_in_units = array();
							foreach ($row_units['units_blocks'] as $row_blocks)
							{
								$lazy_config = $this->menus->get_blocks_url_model($lazy_config, $row_units['units_folder'], $row_blocks);
								if ($lazy_config['lazy_check'])
								{
									$row_blocks['blocks_url'] = $lazy_config['lazy_temp'];
									$blocks_in_units[] = $row_blocks;
								}
							}
							
							if (count($blocks_in_units) > 0)
							{
								$row_units['units_blocks'] = $blocks_in_units;
								$row_units['units_url'] = base_url().$lazy_config['admins_folder']."/".$lazy_config['langs_this']."/".$row_units['units_folder']."/";
								$lazy_config = $this->check_current_block_access_model($lazy_config, $row_units['units_folder']);
								if ( ! $lazy_config['lazy_check'])
								{
									$row_units['units_url'] = $row_units['units_blocks'][0]['blocks_url'];
								}
								
								$units_in_groups[] = $row_units;
							}
						}
					}
					
					if (count($units_in_groups) > 0)
					{
						$row_groups['groups_units'] = $units_in_groups;
						$row_groups['groups_url'] = "";
						$items_menus[] = $row_groups;
					}
				}
			}
			
			if (count($items_menus) > 0)
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_access_menus_ok", TRUE, array(), $items_menus);
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_access_menus_empty", FALSE);
			}
			
			return $lazy_config;
		}
	
		function admins_groups_save_model($lazy_config, $post)
		{
			$pos = $post['groups_pos'];
			if ($pos == "" || $pos == "0")
			{
				$lazy_config = $this->bases_add->get_last_pos_model($lazy_config, "admins_groups", $post['groups_id']);
				if ($lazy_config['lazy_check'])
				{
					$pos = $lazy_config['lazy_temp'];
				}
			}
			else
			{
				$lazy_config = $this->bases_add->move_pos_model($lazy_config, "admins_groups", $post['groups_id'], $pos);
			}
			$data_array = array("groups_pos" => $pos,
								"groups_name" => $post['groups_name'],
								"groups_show" => $post['groups_show'],
								"groups_mark" => $post['groups_mark'],
								"groups_create" => $post['groups_create'],
								"groups_edit" => $post['groups_edit'],
								"groups_delete" => $post['groups_delete'],
								"groups_pub" => $post['groups_pub']);
								
			if ($post['groups_id'] == "")
			{
				$data_array['groups_level'] = 1;
				$data_array['groups_add_hour'] = date("H");
				$data_array['groups_add_minute'] = date("i");
				$data_array['groups_add_second'] = date("s");
				$data_array['groups_add_day'] = date("j");
				$data_array['groups_add_month'] = date("m");
				$data_array['groups_add_year'] = date("Y") - 1900;
				
				$data_array['groups_add_admins_id'] = $this->session->userdata("admins_id");
				
				if ($this->db->insert("admins_groups", $data_array))
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_insert_ok", TRUE);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_insert_save", FALSE);
				}
			}
			else
			{
				$this->db->where("groups_id", $post['groups_id']);
				if ($this->db->update("admins_groups", $data_array))
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_update_ok", TRUE);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_update_save", FALSE);
				}
			}
			
			return $lazy_config;
		}
		
		function admins_groups_edit_model($lazy_config, $id)
		{
			$this->db->select("groups_name, groups_pos, groups_show, groups_mark, groups_create, groups_edit, groups_pub, groups_delete");
			$this->db->where("groups_id", $id);
			$this->db->limit(1);
			
			if ($result = $this->db->get("admins_groups"))
			{
				if ($result->num_rows() > 0)
				{
					$row = $result->row_array();
					
					$lazy_config = $this->langs->get_config_model($lazy_config);
					$lazy_config = $this->langs->get_current_langs_model($lazy_config);
					if($lazy_config['lazy_check'])
					{
						$langs_admin_string = $lazy_config['langs_admin'];
						$langs_admin_array = explode("[:e]", $langs_admin_string);
						
						unset($langs_admin_array[count($langs_admin_array) - 1]);
						
						foreach ($langs_admin_array as $value)
						{
							$lazy_config = $this->langs->get_langs_text_model($lazy_config, $row['groups_name']);
							if ($lazy_config['lazy_check'])
							{
								$row['groups_name_'.$value] = $lazy_config['lazy_temp'];
							}
							else
							{
								$lazy_config = $this->errors->add($lazy_config, "groups_langs_text_get", FALSE);
							}
						}
					}
					else
					{
						$lazy_config = $this->errors->add($lazy_config, "groups_langs_client_empty", FALSE);
					}
					
					$lazy_config = $this->errors->add($lazy_config, "groups_edit_groups_ok", TRUE, array(), $row);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "groups_edit_groups_empty", FALSE);
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "groups_edit_groups_get", FALSE);
			}
			return $lazy_config;
		}
		
		function get_filter_ids_model($lazy_config, $post)
		{
			$this->db->select("groups_id");
			$this->db->where("groups_level >=", $this->session->userdata('groups_level'));
			
			if ($post['filter_show'] != "any")
			{
				$this->db->where("groups_show", $post['filter_show']);
			}
			
			if ($post['filter_mark'] != "any")
			{
				$this->db->where("groups_mark", $post['filter_mark']);
			}
			
			if ($post['groups_create'] != "any")
			{
				$this->db->where("groups_create", $post['groups_create']);
			}
			
			if ($post['groups_edit'] != "any")
			{
				$this->db->where("groups_edit", $post['groups_edit']);
			}
			
			if ($post['groups_delete'] != "any")
			{
				$this->db->where("groups_delete", $post['groups_delete']);
			}
			
			if ($post['groups_pub'] != "any")
			{
				$this->db->where("groups_pub", $post['groups_pub']);
			}
			
			if ($post['filter_date'] != "any")
			{
				if ($post['filter_day'] != "any")
				{
					$this->db->where("groups_".$post['filter_date']."_day", $post['filter_day']);
				}
				
				if ($post['filter_month'] != "any")
				{
					$this->db->where("groups_".$post['filter_date']."_month", $post['filter_month']);
				}
				
				if ($post['filter_year'] != "any")
				{
					$this->db->where("groups_".$post['filter_date']."_year", $post['filter_year']);
				}
			}
			
			if ($result = $this->db->get("admins_groups"))
			{
				if ($result->num_rows() > 0)
				{
					$items = array();
					foreach ($result->result_array() as $row)
					{
						$items[] = $row['groups_id'];
					}
					
					if ($post['filter_search'] != "")
					{
						$lazy_config = $this->search_items_model($lazy_config, "admins_groups", array("groups_name"), $post['filter_search']);
						if ($lazy_config['lazy_check'])
						{
							$items = array_intersect($items, $lazy_config['lazy_temp']);
							$new_items = array();
							foreach ($items as $value)
							{
								$new_items[] = $value;
							}
							$items = $new_items;
						}
					}
					
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_ok", TRUE, array(), $items);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_empty", TRUE);
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_groups_get", FALSE);
			}
			
			return $lazy_config;
		}
		
		function admins_groups_get_model($lazy_config, $post)
		{
			$lazy_config = $this->get_filter_ids_model($lazy_config, $post);
			if ($lazy_config['lazy_check'])
			{
				$items = $lazy_config['lazy_temp'];
				
				$lazy_config = $this->langs->get_config_model($lazy_config);
				$lazy_config = $this->langs->get_current_langs_model($lazy_config);
				
				if (count($items) > 0)
				{
					$sort = explode("-", $post['filter_sort']);
					if ($sort[0] == "add" || $sort[0] == "edit" || $sort[0] == "pub")
					{
						$this->db->order_by("groups_".$sort[0]."_year ".$sort[1].", groups_".$sort[0]."_month ".$sort[1].", groups_".$sort[0]."_day ".$sort[1].", groups_".$sort[0]."_hour ".$sort[1].", groups_".$sort[0]."_minute ".$sort[1].", groups_".$sort[0]."_second ".$sort[1]);
					}
					else
					{
						if ($sort[0] == "pos")
						{
							$this->db->order_by("groups_".$sort[0], $sort[1]);
						}
					}
					
					if ($sort[0] == "name")
					{
						$this->db->select("groups_id, groups_pos, groups_name, groups_show, groups_mark, groups_create, groups_edit, groups_delete, groups_pub, groups_add_hour, groups_add_minute, groups_add_second, groups_add_day, groups_add_month, groups_add_year, groups_add_admins_id");
						$this->db->where_in("groups_id", $items);
						
						if ($result = $this->db->get("admins_groups"))
						{
							if ($result->num_rows() > 0)
							{
								$lazy_config['lazy_rows'] = $result->num_rows();
								$lazy_config['lazy_pages'] = ceil($lazy_config['lazy_rows'] / $post['filter_onpage']);
						
								$sort_array = array();
								$values_array = array();
								foreach ($result->result_array() as $row)
								{
									$items_line = "";
									
									$lazy_config = $this->langs->get_langs_text_model($lazy_config, $row['groups_name']);
									$row['groups_name'] = $lazy_config['lazy_temp'];
									
									$sort_array[$row['groups_id']] = $row['groups_'.$sort[0]];
									
									foreach ($row as $key => $value)
									{
										$items_line .= $key."[:k]".$value."[:e]";

										if ($key == "groups_add_admins_id")
										{
											$lazy_config = $this->bases_add->get_fields_model($lazy_config, "admins_items", $value, "admins_login", TRUE);
											
											if ($lazy_config['lazy_check'])
											{
												$items_line .= "groups_add_admins_login[:k]".$lazy_config['lazy_temp']."[:e]";
											}
										}
									}
									
									$values_array[$row['groups_id']] = $items_line."[:l]";
								}
								
								if ($sort[1] == "asc")
								{
									asort($sort_array);
								}
								else
								{
									arsort($sort_array);
								}

								$items = "";
								$index = 0;
								foreach ($sort_array as $key => $value)
								{
									if ($index >= ($post['filter_thispage'] - 1) * $post['filter_onpage'] && $index < $post['filter_thispage'] * $post['filter_onpage'])
									{
										$items .= $values_array[$key];
									}
									$index++;
								}
								
								$lazy_config = $this->errors->add($lazy_config, "admins_groups_info_ok", TRUE, array(), $items);
							}
							else
							{
								$lazy_config = $this->errors->add($lazy_config, "admins_groups_info_empty", TRUE);
							}
						}
						else
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_groups_info_get", FALSE);
						}
					}
					else
					{
						$this->db->select("groups_id, groups_pos, groups_name, groups_show, groups_mark, groups_create, groups_edit, groups_delete, groups_pub, groups_add_hour, groups_add_minute, groups_add_second, groups_add_day, groups_add_month, groups_add_year, groups_add_admins_id");
						$this->db->where_in("groups_id", $items);
						
						$lazy_config['lazy_rows'] = $this->db->count_all_results("admins_groups");
						$lazy_config['lazy_pages'] = ceil($lazy_config['lazy_rows'] / $post['filter_onpage']);

						$query = str_replace("COUNT(*) AS `numrows`", "*", $this->db->last_query()).(((($post['filter_thispage'] - 1) * $post['filter_onpage']) > 0) ? "\nLIMIT ".(($post['filter_thispage'] - 1) * $post['filter_onpage']).", ".$post['filter_onpage'] : "\nLIMIT ".$post['filter_onpage']);
						
						if($result = $this->db->query($query))
						{
							if($result->num_rows() > 0)
							{
								$items = "";
								foreach ($result->result_array() as $row)
								{
									$items_line = "";
									
									$lazy_config = $this->langs->get_langs_text_model($lazy_config, $row['groups_name']);
									$row['groups_name'] = $lazy_config['lazy_temp'];
									
									foreach ($row as $key => $value)
									{
										$items_line .= $key."[:k]".$value."[:e]";

										if ($key == "groups_add_admins_id")
										{
											$lazy_config = $this->bases_add->get_fields_model($lazy_config, "admins_items", $value, "admins_login", TRUE);
											
											if ($lazy_config['lazy_check'])
											{
												$items_line .= "groups_add_admins_login[:k]".$lazy_config['lazy_temp']."[:e]";
											}
										}
									}
									
									$items .= $items_line."[:l]";
								}
								
								$lazy_config = $this->errors->add($lazy_config, "admins_groups_info_ok", TRUE, array(), $items);
							}
							else
							{
								$lazy_config = $this->errors->add($lazy_config, "admins_groups_info_empty", TRUE);
							}
						}
						else
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_groups_info_get", FALSE);
						}
					}
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_id_empty", TRUE);
				}
			}

			return $lazy_config;
		}
		
		function search_items_model($lazy_config, $table, $fields, $search)
		{
			$words = explode(" ", $search);
			for ($w = 0; $w < count($words); $w++)
			{
				$words[$w] = trim($words[$w]);
				$words[$w] = str_replace(",", "", $words[$w]);
				$words[$w] = str_replace(".", "", $words[$w]);
				$words[$w] = str_replace("-", "", $words[$w]);
				$words[$w] = str_replace("_", "", $words[$w]);
				
				if (strlen($words[$w]) <= 2)
				{
					unset($words[$w]);
				}
			}
			
			if (count($words) > 0)
			{
				$search_items = array();
				for($w = 0; $w < count($words); $w++)
				{
					$temp = array();
					
					$this->db->select("groups_id");
					
					for($f = 0; $f < count($fields); $f++)
					{
						if ($f == 0)
						{
							$this->db->like($fields[$f], $words[$w]);
						}
						else
						{
							$this->db->or_like($fields[$f], $words[$w]);
						}
					}
					
					if ($result = $this->db->get("admins_groups"))
					{
						if ($result->num_rows() > 0)
						{
							$temp_items = array();
							foreach ($result->result_array() as $row)
							{
								$temp_items[] = $row['groups_id'];
							}
							$search_items[] = $temp_items;
							$lazy_config = $this->errors->add($lazy_config, "admins_groups_search_word_ok", TRUE, array($table, $words[$w]));
						}
						else
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_groups_search_word_empty", TRUE, array($table, $words[$w]));
						}
					}
					else
					{
						$lazy_config = $this->errors->add($lazy_config, "admins_groups_search_word_get", FALSE, array($table, $words[$w]));
					}
				}
				
				if (count($search_items) > 0)
				{
					$ids = array();
					$ratio = array();
				
					for($i = 0; $i < count($search_items); $i++)
					{
						foreach ($search_items[$i] as $key => $value)
						{
							if ( ! isset($ids["a".$value]))
							{
								$ids["a".$value] = $value;
								$ratio["a".$value] = 1;
							}
							else
							{
								$ratio["a".$value]++;
							}
						}
					}
					
					arsort($ratio);
					$result = array();
					foreach ($ratio as $key => $value)
					{
						$result[] = $ids[$key];
					}
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_search_ok", TRUE, array($table), $result);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_search_empty", TRUE, array($table), array());
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_groups_search_no_correct", FALSE);
			}
			
			return $lazy_config;
		}
		
		function admins_groups_delete_model($lazy_config, $post)
		{
			$this->db->where("groups_id", $post['items_id']);
			$this->db->limit(1);
			
			if ($this->db->count_all_results("admins_items") > 0)
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_groups_delete_is_admins", FALSE, array($post['items_id']));
			}
			else
			{
				$this->db->where("groups_id", $post['items_id']);
				if ($this->db->delete("admins_groups"))
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_delete_ok", TRUE, array($post['items_id']));
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_groups_delete_save", FALSE, array($post['items_id']));
				}
			}
			
			return $lazy_config;
		}
		
		function admins_groups_actions_model($lazy_config, $post)
		{
			$ids = array();
			if ($post['ids'] == "all")
			{
				$lazy_config = $this->get_filter_ids_model($lazy_config, $post);
				if ($lazy_config['lazy_check'])
				{
					$ids = $lazy_config['lazy_temp'];
				}
			}
			else
			{
				$ids = array_diff(explode("[:e]", $post['ids']), array(''));
			}
			
			if (count($ids) > 0)
			{
				for ($i = 0; $i < count($ids); $i++)
				{
					if ($post['actions'] == "delete")
					{
						$post['items_id'] = $ids[$i];
						$lazy_config = $this->admins_groups_delete_model($lazy_config, $post);
					}
					else
					{
						$part = explode("-", $post['actions']);
						$lazy_config = $this->bases_add->change_checks_model($lazy_config, "admins_groups", $part[0], $ids[$i], $part[1]);
					}
				}
				$lazy_config = $this->errors->add($lazy_config, "admins_groups_actions_ok", TRUE);
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_groups_actions_empty", FALSE);
			}
			
			return $lazy_config;
		}
		
		function get_groups_list_model($lazy_config)
		{
				
			$this->db->order_by("groups_pos", "asc");
			$this->db->select("groups_id, groups_name, groups_create, groups_edit, groups_delete, groups_pub");
			$this->db->where("groups_show", TRUE);

			if ($result = $this->db->get("admins_groups"))
			{
				if ($result->num_rows() > 0)
				{	
					$items = array();
					foreach ($result->result_array() as $row)
					{
						$lazy_config = $this->langs->get_langs_text_model($lazy_config, $row['groups_name']);
						
						$items_array = array();
						$items_array['groups_id'] = $row['groups_id'];
						$items_array['groups_name'] = $lazy_config['lazy_temp'];
						$items_array['groups_create'] = $row['groups_create'];
						$items_array['groups_edit'] = $row['groups_edit'];
						$items_array['groups_delete'] = $row['groups_delete'];
						$items_array['groups_pub'] = $row['groups_pub'];
						
						$items[] = $items_array;
					}
					
					$lazy_config = $this->errors->add($lazy_config, "groups_list_ok", TRUE, array("admins_groups"), $items);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "groups_list_empty", FALSE, array("admins_groups"));
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "groups_list_get", FALSE, array("admins_groups"));
			}
			
			return $lazy_config;
		}
		
		function admins_save_model($lazy_config, $post)
		{
			$this->db->select("admins_login");
			$this->db->where("admins_login", $post['admins_login']);
			$this->db->limit(1);
			if ($this->db->count_all_results('admins_items') > 0 && $post['admins_id'] == "")
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_login_isset", FALSE);
			}
			else
			{
				$this->db->select("admins_email");
				$this->db->where("admins_email", $post['admins_email']);
				$this->db->limit(1);
				if ($this->db->count_all_results('admins_items') > 0 && $post['admins_id'] == "" && $post['admins_email'] != "")
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_email_isset", FALSE);
				}
				else
				{
					$pos = $post['admins_pos'];
					if ($pos == "" || $pos == "0")
					{
						$lazy_config = $this->bases_add->get_last_pos_model($lazy_config, "admins_items", $post['admins_id']);
						if ($lazy_config['lazy_check'])
						{
							$pos = $lazy_config['lazy_temp'];
						}
					}
					else
					{
						$lazy_config = $this->bases_add->move_pos_model($lazy_config, "admins_items", $post['admins_id'], $pos);
					}
					
					$data_array = array("admins_pos" => $pos,
										"groups_id" => $post['groups_id'],
										"admins_login" => $post['admins_login'],
										"admins_name" => $post['admins_name'],
										"admins_email" => $post['admins_email'],
										"admins_contacts" => $post['admins_contacts'],
										"admins_active" => $post['admins_active'],
										"admins_mark" => $post['admins_mark'],
										"admins_type" => $post['admins_access']);
										
					if ($post['admins_pass'] != "")
					{
						$data_array['admins_pass'] = md5($post['admins_pass']);
					}
					
					if ($post['admins_id'] == "")
					{
						$data_array['admins_add_hour'] = date("H");
						$data_array['admins_add_minute'] = date("i");
						$data_array['admins_add_second'] = date("s");
						$data_array['admins_add_day'] = date("j");
						$data_array['admins_add_month'] = date("m");
						$data_array['admins_add_year'] = date("Y") - 1900;
						$data_array['admins_add_admins_id'] = $this->session->userdata("admins_id");
						
						if ($this->db->insert("admins_items", $data_array))
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_insert_ok", TRUE);
							
							if (($post['admins_access'] == 'custom' || $post['admins_access'] == 'noone') && $post['admins_type'] != '')
							{	
								$access_array['admins_id'] = $this->db->insert_id();
								$access_array['blocks_type'] = 'admin';
								$admins_rules = array_diff(explode("[:e]", $post['admins_rules']), array(''));
								
								foreach($admins_rules as $val)
								{
									$rules = explode("-", $val);
									$access_array['blocks_unit'] = $rules[0];
									$access_array['blocks_id'] = $rules[1];
									$access_array['blocks_'.$post['admins_type']] = TRUE;
									
									if ($this->db->insert("admins_access", $access_array))
									{
										$lazy_config = $this->errors->add($lazy_config, "admins_access_insert_ok", TRUE);
									}
									else
									{
										$lazy_config = $this->errors->add($lazy_config, "admins_access_insert_save", FALSE);
									}
								}
							}
						}
						else
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_insert_save", FALSE);
						}
					}
					else
					{
						$this->db->where("admins_id", $post['admins_id']);
						if ($this->db->update("admins_items", $data_array))
						{
							$this->db->where("admins_id", $post['admins_id']);
							if ($this->db->delete("admins_access"))
							{
								$lazy_config = $this->errors->add($lazy_config, "admins_access_delete_ok", TRUE);
								
								if (($post['admins_access'] == 'custom' || $post['admins_access'] == 'noone') && $post['admins_type'] != '')
								{	
									$access_array['admins_id'] = $post['admins_id'];
									$access_array['blocks_type'] = 'admin';
									$admins_rules = array_diff(explode("[:e]", $post['admins_rules']), array(''));
									
									foreach($admins_rules as $val)
									{
										$rules = explode("-", $val);
										$access_array['blocks_unit'] = $rules[0];
										$access_array['blocks_id'] = $rules[1];
										$access_array['blocks_'.$post['admins_type']] = TRUE;
										
										if ($this->db->insert("admins_access", $access_array))
										{
											$lazy_config = $this->errors->add($lazy_config, "admins_access_update_ok", TRUE);
										}
										else
										{
											$lazy_config = $this->errors->add($lazy_config, "admins_access_update_save", FALSE);
										}
									}
								}
							}
							else
							{
								$lazy_config = $this->errors->add($lazy_config, "admins_access_delete_save", FALSE);
							}
							
							
							$lazy_config = $this->errors->add($lazy_config, "admins_update_ok", TRUE);
						}
						else
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_update_save", FALSE);
						}
					}
				}
			}
			
			return $lazy_config;
		}
		
		function admins_get_model($lazy_config, $post)
		{
			$lazy_config = $this->get_admins_filter_ids_model($lazy_config, $post);
			if ($lazy_config['lazy_check'])
			{
				$items = $lazy_config['lazy_temp'];
				
				$lazy_config = $this->langs->get_config_model($lazy_config);
				$lazy_config = $this->langs->get_current_langs_model($lazy_config);
				
				if (count($items) > 0)
				{
					$sort = explode("-", $post['filter_sort']);
					if ($sort[0] == "add" || $sort[0] == "edit" || $sort[0] == "pub")
					{
						$this->db->order_by("admins_".$sort[0]."_year ".$sort[1].", admins_".$sort[0]."_month ".$sort[1].", admins_".$sort[0]."_day ".$sort[1].", admins_".$sort[0]."_hour ".$sort[1].", admins_".$sort[0]."_minute ".$sort[1].", admins_".$sort[0]."_second ".$sort[1]);
					}
					else
					{
						if ($sort[0] == "pos" || $sort[0] == "login")
						{
							$this->db->order_by("admins_".$sort[0], $sort[1]);
						}
					}
					$this->db->select("admins_id, groups_id, admins_pos, admins_name, admins_login, admins_type, admins_email, admins_contacts, admins_active, admins_mark, admins_add_hour, admins_add_minute, admins_add_second, admins_add_day, admins_add_month, admins_add_year, admins_add_admins_id");
					$this->db->where_in("admins_id", $items);
					
					$lazy_config['lazy_rows'] = $this->db->count_all_results("admins_items");
					$lazy_config['lazy_pages'] = ceil($lazy_config['lazy_rows'] / $post['filter_onpage']);

					$query = str_replace("COUNT(*) AS `numrows`", "*", $this->db->last_query()).(((($post['filter_thispage'] - 1) * $post['filter_onpage']) > 0) ? "\nLIMIT ".(($post['filter_thispage'] - 1) * $post['filter_onpage']).", ".$post['filter_onpage'] : "\nLIMIT ".$post['filter_onpage']);
					
					if($result = $this->db->query($query))
					{
						if($result->num_rows() > 0)
						{
							$items = "";
							foreach ($result->result_array() as $row)
							{
								$items_line = "";
								
								foreach ($row as $key => $value)
								{
									$items_line .= $key."[:k]".$value."[:e]";

									if ($key == "admins_add_admins_id")
									{
										$lazy_config = $this->bases_add->get_fields_model($lazy_config, "admins_items", $value, "admins_login", TRUE);
										
										if ($lazy_config['lazy_check'])
										{
											$items_line .= "admins_add_admins_login[:k]".$lazy_config['lazy_temp']."[:e]";
										}
									}
									
									if ($key == "groups_id")
									{
										$lazy_config = $this->bases_add->get_fields_model($lazy_config, "admins_groups", $value, "groups_name, groups_create, groups_edit, groups_delete, groups_pub", FALSE);
										
										if ($lazy_config['lazy_check'])
										{
											$temp_groups = $lazy_config['lazy_temp'];
											foreach ($temp_groups as $group => $val)
											{
												$items_line .= $group."[:k]".$val."[:e]";
												
												if ($group == "groups_name")
												{
													$lazy_config = $this->langs->get_langs_text_model($lazy_config, $val);
											
													$items_line .= "admins_groups[:k]".$lazy_config['lazy_temp']."[:e]";
												}
											}
											
										}
										
									}
								}
								
								$items .= $items_line."[:l]";
							}
							
							$lazy_config = $this->errors->add($lazy_config, "admins_info_ok", TRUE, array(), $items);
						}
						else
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_info_empty", TRUE);
						}
					}
					else
					{
						$lazy_config = $this->errors->add($lazy_config, "admins_info_get", FALSE);
					}
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_id_empty", TRUE);
				}
			}

			return $lazy_config;
		}
		
		function get_admins_filter_ids_model($lazy_config, $post)
		{
			$this->db->select("admins_id");
			
			if ($this->session->userdata('groups_id') != 1)
			{
				$this->db->where("groups_id <>", 1);
			}
			
			if ($post['filter_active'] != "any")
			{
				$this->db->where("admins_active", $post['filter_active']);
			}
			
			if ($post['filter_mark'] != "any")
			{
				$this->db->where("admins_mark", $post['filter_mark']);
			}
			
			if ($post['filter_date'] != "any")
			{
				if ($post['filter_day'] != "any")
				{
					$this->db->where("admins_".$post['filter_date']."_day", $post['filter_day']);
				}
				
				if ($post['filter_month'] != "any")
				{
					$this->db->where("admins_".$post['filter_date']."_month", $post['filter_month']);
				}
				
				if ($post['filter_year'] != "any")
				{
					$this->db->where("admins_".$post['filter_date']."_year", $post['filter_year']);
				}
			}
			
			if ($result = $this->db->get("admins_items"))
			{
				if ($result->num_rows() > 0)
				{
					$items = array();
					foreach ($result->result_array() as $row)
					{
						$items[] = $row['admins_id'];
					}
					
					if ($post['filter_search'] != "")
					{
						$lazy_config = $this->search_admins_items_model($lazy_config, "admins_items", array("admins_name, admins_login, admins_email, admins_contacts"), $post['filter_search']);
						if ($lazy_config['lazy_check'])
						{
							$items = array_intersect($items, $lazy_config['lazy_temp']);
							$new_items = array();
							foreach ($items as $value)
							{
								$new_items[] = $value;
							}
							$items = $new_items;
						}
					}
					
					$lazy_config = $this->errors->add($lazy_config, "admins_ok", TRUE, array(), $items);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_empty", TRUE);
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_get", FALSE);
			}
			
			return $lazy_config;
		}
		
		function search_admins_items_model($lazy_config, $table, $fields, $search)
		{
			$words = explode(" ", $search);
			for ($w = 0; $w < count($words); $w++)
			{
				$words[$w] = trim($words[$w]);
				$words[$w] = str_replace(",", "", $words[$w]);
				$words[$w] = str_replace(".", "", $words[$w]);
				$words[$w] = str_replace("-", "", $words[$w]);
				$words[$w] = str_replace("_", "", $words[$w]);
				
				if (strlen($words[$w]) <= 3)
				{
					unset($words[$w]);
				}
			}
			
			if (count($words) > 0)
			{
				$search_items = array();
				for($w = 0; $w < count($words); $w++)
				{
					$temp = array();
					
					$this->db->select("admins_id");
					
					for($f = 0; $f < count($fields); $f++)
					{
						if ($f == 0)
						{
							$this->db->like($fields[$f], $words[$w]);
						}
						else
						{
							$this->db->or_like($fields[$f], $words[$w]);
						}
					}
					
					if ($result = $this->db->get("admins_items"))
					{
						if ($result->num_rows() > 0)
						{
							$temp_items = array();
							foreach ($result->result_array() as $row)
							{
								$temp_items[] = $row['admins_id'];
							}
							$search_items[] = $temp_items;
							$lazy_config = $this->errors->add($lazy_config, "admins_search_word_ok", TRUE, array($table, $words[$w]));
						}
						else
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_search_word_empty", TRUE, array($table, $words[$w]));
						}
					}
					else
					{
						$lazy_config = $this->errors->add($lazy_config, "admins_search_word_get", FALSE, array($table, $words[$w]));
					}
				}
				
				if (count($search_items) > 0)
				{
					$ids = array();
					$ratio = array();
				
					for($i = 0; $i < count($search_items); $i++)
					{
						foreach ($search_items[$i] as $key => $value)
						{
							if ( ! isset($ids["a".$value]))
							{
								$ids["a".$value] = $value;
								$ratio["a".$value] = 1;
							}
							else
							{
								$ratio["a".$value]++;
							}
						}
					}
					
					arsort($ratio);
					$result = array();
					foreach ($ratio as $key => $value)
					{
						$result[] = $ids[$key];
					}
					$lazy_config = $this->errors->add($lazy_config, "admins_search_ok", TRUE, array($table), $result);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_search_empty", TRUE, array($table), array());
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_search_no_correct", FALSE);
			}
			
			return $lazy_config;
		}
		
		function admins_delete_model($lazy_config, $post)
		{
			$this->db->where("admins_id", $post['items_id']);
			$this->db->limit(1);
			
			$this->db->where("admins_id", $post['items_id']);
			if ($this->db->delete("admins_items"))
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_delete_ok", TRUE, array($post['items_id']));
				
				$this->db->where("admins_id", $post['items_id']);
				if ($this->db->delete("admins_access"))
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_delete_access_ok", TRUE, array($post['items_id']));
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_delete_access_save", FALSE, array($post['items_id']));
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_delete_save", FALSE, array($post['items_id']));
			}
			
			return $lazy_config;
		}
		
		function admins_actions_model($lazy_config, $post)
		{
			$ids = array();
			if ($post['ids'] == "all")
			{
				$lazy_config = $this->get_admins_filter_ids_model($lazy_config, $post);
				if ($lazy_config['lazy_check'])
				{
					$ids = $lazy_config['lazy_temp'];
				}
			}
			else
			{
				$ids = array_diff(explode("[:e]", $post['ids']), array(''));
			}
			
			if (count($ids) > 0)
			{
				for ($i = 0; $i < count($ids); $i++)
				{
					if ($post['actions'] == "delete")
					{
						$post['items_id'] = $ids[$i];
						$lazy_config = $this->admins_delete_model($lazy_config, $post);
					}
					else
					{
						$part = explode("-", $post['actions']);
						$lazy_config = $this->bases_add->change_checks_model($lazy_config, "admins_items", $part[0], $ids[$i], $part[1]);
					}
				}
				$lazy_config = $this->errors->add($lazy_config, "admins_actions_ok", TRUE);
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_actions_empty", FALSE);
			}
			
			return $lazy_config;
		}
		
		function admins_edit_model($lazy_config, $id)
		{
			$this->db->select("groups_id, admins_pos, admins_name, admins_login, admins_type, admins_email, admins_contacts, admins_active, admins_mark");
			$this->db->where("admins_id", $id);
			$this->db->limit(1);
			
			if ($result = $this->db->get("admins_items"))
			{
				if ($result->num_rows() > 0)
				{
					$row = $result->row_array();
					
					if ($row['admins_type'] == "custom")
					{
						$this->db->select("blocks_id, blocks_type, blocks_unit, blocks_only, blocks_except");
						$this->db->where("admins_id", $id);
						
						if ($result = $this->db->get("admins_access"))
						{
							if ($result->num_rows() > 0)
							{
								foreach ($result->result_array() as $access)
								{	
									$row[$access['blocks_unit']."-".$access['blocks_id']] = ($access['blocks_only'] == TRUE) ? "true" : "false";
								}
							}
							else
							{
								$lazy_config = $this->errors->add($lazy_config, "admins_access_edit_empty", FALSE);
							}
						}
						else
						{
							$lazy_config = $this->errors->add($lazy_config, "admins_access_edit_get", FALSE);
						}
						
					}
					
					$lazy_config = $this->errors->add($lazy_config, "admins_edit_ok", TRUE, array(), $row);
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "admins_edit_empty", FALSE);
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "admins_edit_get", FALSE);
			}
			return $lazy_config;
		}
		
	}
	
/* End of file admins_admin.php */