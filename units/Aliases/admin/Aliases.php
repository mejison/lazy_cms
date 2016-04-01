<?php

	class Aliases extends Unit
	{
		var $_unit = "";
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($info, $params = array())
		{
			System::$lazy['params'] = $params;
			return $this->files->block($info, $this->_unit);
		}

		function check($unit)
		{	
			if ($unit != "")
			{
				$this->db->where("items_folder", $unit);
				$this->db->limit(1);
				
				if ( ! $this->db->count_all("units_items"))
				{
					$this->debug->not_found();
				}
			}
			
			return TRUE;
		}
		
		function info($unit)
		{
			if ($unit == "")
			{
				$this->db->select("items_folder");
				$this->db->where("items_default", TRUE);
				$this->db->limit(1);

				$_values['table'] = "units_items";
				$row = $this->db->get($_values['table']);
				if ( ! count($row))
				{
					$this->debug->not_found();
				}
			}
			else
			{
				$this->db->select("items_folder");
				$this->db->where("items_folder", $unit);
				$this->db->limit(1);
				
				$_values['table'] = "units_items";
				$row = $this->db->get($_values['table']);
			}

			if (count($row))
			{
				$this->db->select("blocks_id, blocks_folder");
				$this->db->where("blocks_default", TRUE);
				$this->db->where("blocks_type", TYPE);
				$this->db->limit(1);

				$_values['table'] = $row['items_folder']."_blocks";
				$result = $this->db->get($_values['table']);
				
				if (count($result))
				{
					System::$lazy['_this']['unit'] = $row['items_folder'];
					System::$lazy['_this']['block'] = $result['blocks_id'];
					System::$lazy['_this']['info'] = $this->blocks->info($result['blocks_folder']);
					
					return $this->debug->add("ok_aliases_info");
				}	
			}

			$this->debug->not_found();
		}
		
		function for_items($items_id, $unit = "", $lang = "")
		{
			$unit = ($unit == "") ? System::$lazy['this_page']['unit'] : $unit;
			$lang = ($lang == "") ? Locals::$langs['this'] : $lang;
			
			$this->db->select("aliases_texts.items_name");
			$this->db->where("aliases_items.items_id", $items_id);
			$this->db->where("aliases_items.items_unit", $unit);
			$this->db->where("aliases_texts.langs_code", $lang);
			$this->db->where("aliases_items.items_table", "items");
			$this->db->join("aliases_texts", "aliases_items.aliases_id = aliases_texts.items_id");
			$this->db->limit(1);
			
			if ($result = $this->db->get("aliases_items"))
			{
				if ($result->num_rows() > 0)
				{
					$row = $result->row_array();
					$this->debug->log("aliases_ok", OK);
					return $row['items_name'];
				}
				else
				{
					return $this->debug->log("aliases_empty");
				}
			}
			else
			{
				return $this->debug->log("aliases_get");
			}
		}
		
		function for_blocks($blocks_id, $unit = "", $lang = "")
		{
			$unit = ($unit == "") ? System::$lazy['_this']['unit'] : $unit;
			$langs_code = ($lang == "") ? Langs::$_config['this'] : $lang;
			
			$this->db->select("aliases_texts.items_name");
			$this->db->where("aliases_items.items_id", $blocks_id);
			$this->db->where("aliases_items.items_unit", $unit);
			$this->db->where("aliases_texts.langs_code", $langs_code);
			$this->db->where("aliases_items.items_table", "blocks");
			$this->db->join("aliases_texts", "aliases_items.aliases_id = aliases_texts.items_id");
			$this->db->limit(1);
			
			if ($result = $this->db->get("aliases_items"))
			{
				if ($result->num_rows() > 0)
				{
					$row = $result->row_array();
					$this->debug->log("aliases_ok", OK);
					return $row['items_name'];
				}
				else
				{
					return $this->debug->log("aliases_empty");
				}
			}
			else
			{
				return $this->debug->log("aliases_get");
			}
		}
		
		function unique($post)
		{ 
			$alias = (isset($post['number'])) ? $post['aliases']."-".$post['number'] : $post['aliases'];
			$this->db->select("aliases_items.items_id, aliases_items.items_unit, aliases_items.blocks_id");
			$this->db->where("aliases_texts.items_name", $alias);
			$this->db->join("aliases_texts", "aliases_items.aliases_id = aliases_texts.items_id");
			$this->db->limit(1);
			
			if ($this->db->count_all_results("aliases_items") > 0)
			{
				$post['number'] = (isset($post['number'])) ? ($post['number'] + 1) : 1;
				$result = $this->unique($post);
				$alias = $result['alias'];
			}
			else
			{
				$this->debug->log("unique_ok", OK);
			}
			
			return array("alias" => $alias,
						 "id" => $post['id'],
						 "change" => (isset($post['number'])) ? TRUE : FALSE);
		}
		
		function save($unit, $items_id, $table, $data)
		{
			$return = 1;
			$_values['table'] = "aliases_items";
			$data_array = array("items_table" => $table,
								"items_unit" => $unit,
								"items_id" => $items_id);
			$this_id = 0;
			
			$this->db->select('aliases_id');
			$this->db->where('items_table', $table);
			$this->db->where('items_id', $items_id);
			$this->db->where('items_unit', $unit);
			$this->db->limit(1);
			
			if ($result = $this->db->get($_values['table']))
			{
				if ($result->num_rows() > 0)
				{
					$row = $result->row_array();
					$this_id = $row['aliases_id'];
					$this->db->where('aliases_id', $this_id);
					if ($this->db->update($_values['table'], $data_array))
					{
						$return *= $this->debug->log("update_aliases_ok", OK);
					}
					else
					{
						return $this->debug->log("update", ERROR, $_values);
					}
				}
				else
				{
					if ($this->db->insert($_values['table'], $data_array))
					{
						$this_id = $this->db->insert_id();
						$return *= $this->debug->log("insert_aliases_ok", OK);
					}
					else
					{
						return $this->debug->log("insert", ERROR, $_values);
					}
				}
			}
			else 
			{
				return $this->debug->log("get", ERROR, $_values);
			}
			
			if ($this_id != 0)
			{
				$_values['table'] = "aliases_texts";
				$text_array = array("items_id" => $this_id);
				foreach (Locals::$langs['client'] as $lang)
				{
					$text_array['langs_code'] = $lang;
					$text_array['items_name'] = "";
					if (isset($data[$lang]) && $data[$lang] != "")
					{
						$temp = explode($_SERVER['HTTP_HOST']."/".$lang."/", $data[$lang]);
						if (count($temp) > 1)
						{
							$text_array['items_name'] = trim($temp[1], '/');
						}
					}
					
					if ($text_array['items_name'] != "")
					{
						$this->db->select('texts_id');
						$this->db->where('items_id', $this_id);
						$this->db->where('langs_code', $lang);
						if ($this->db->count_all_results($_values['table']) > 0)
						{
							$this->db->where('items_id', $this_id);
							$this->db->where('langs_code', $lang);
							if ($this->db->update($_values['table'], $text_array))
							{
								$return *= $this->debug->log("update_texts_ok", OK);
							}
							else
							{
								$this->debug->log("update", ERROR, $_values);
							}
						}
						else 
						{
							if ($this->db->insert($_values['table'], $text_array))
							{
								$return *= $this->debug->log("insert_texts_ok", OK);
							}
							else
							{
								$this->debug->log("insert", ERROR, $_values);
							}
						}
					}
				}
			}

			return $return;
		}
		
		function edit($unit, $items_id, $table, $field)
		{
			$_values['table'] = "aliases_items";
			$return = array();
			
			$this->db->select('aliases_id');
			$this->db->where('items_table', $table);
			$this->db->where('items_id', $items_id);
			$this->db->where('items_unit', $unit);
			$this->db->limit(1);
		
			if ($result = $this->db->get($_values['table']))
			{
				if ($result->num_rows() > 0)
				{
					$row = $result->row_array();
					$_values['table'] = "aliases_texts";
					
					$this->db->select('items_name, langs_code');
					$this->db->where('items_id', $row['aliases_id']);
					if ($result = $this->db->get($_values['table']))
					{
						if ($result->num_rows() > 0)
						{
							foreach ($result->result_array() as $row)
							{
								$return['aliases_'.$field.'-'.$row['langs_code']] = $row['items_name'];
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
				else
				{
					$this->debug->log("empty", ERROR, $_values);
				}
			}
			else 
			{
				$this->debug->log("get", ERROR, $_values);
			}
			
			return $return;
		}
		
		function info_client($aliases)
		{
			if ($aliases == "404_client")
			{
				$object = "debug";
				System::$lazy['this_page']['unit'] = $object;
				System::$lazy['this_page']['id'] = 0;
				System::$lazy['this_page']['block'] = $aliases;
				System::$lazy['this_page']['cat'] = 0;
				System::$lazy['this_page']['alias'] = $aliases;
				System::$lazy['this_page']['type'] = ($this->uri->total_segments() - 1);
				System::$lazy['this_page']['info'] = $this->blocks->info($aliases, $object);

				return FALSE;
			}
			else
			{
				if ($aliases == "")
				{
					$this->db->select("items_id, items_unit, items_table");
					$this->db->where("menus_default", TRUE);
					$this->db->limit(1);
					
					$result = $this->db->get("menus_items");
				}
				else
				{
					$this->db->select("aliases_items.items_id, aliases_items.items_unit, aliases_items.items_table");
					$this->db->where("aliases_texts.items_name", $aliases);
					$this->db->where("langs_code", Locals::$langs['this']);
					$this->db->join("aliases_items", "aliases_texts.items_id = aliases_items.aliases_id");
					$this->db->limit(1);
					
					$result = $this->db->get("aliases_texts");
				}
				
				if ($result)
				{
					if ($result->num_rows() > 0)
					{
						$row = $result->row_array();
						
						System::$lazy['this_page']['unit'] = $row['items_unit'];
						System::$lazy['this_page']['id'] = ($row['items_table'] == "items") ? $row['items_id'] : 0;
						System::$lazy['this_page']['block'] = ($row['items_table'] == "blocks") ? $row['items_id'] : 0;
						System::$lazy['this_page']['cat'] = ($row['items_table'] == "cats") ? $row['items_id'] : 0;
						System::$lazy['this_page']['alias'] = $aliases;
						System::$lazy['this_page']['type'] = ($this->uri->total_segments() - 1);
						System::$lazy['this_page']['info'] = FALSE;
						if (System::$lazy['this_page']['block'] != 0)
						{
							$this->db->select('blocks_folder');
							$this->db->where('blocks_id', System::$lazy['this_page']['block']);
							$this->db->limit(1);
							
							if ($result = $this->db->get($row['items_unit']."_blocks"))
							{
								if ($result->num_rows() > 0)
								{
									$row = $result->row_array();
									System::$lazy['this_page']['info'] = $this->blocks->info($row['blocks_folder']);
								}
							}
						}
						else
						{
							$this->db->select('blocks_folder');
							if (System::$lazy['this_page']['id'] != 0)
							{
								$this->db->where('url_type', 1);
							}
							else
							{
								$this->db->where('blocks_default', TRUE);
							}
							$this->db->where('blocks_type', System::$type);
							$this->db->limit(1);
							
							if ($result = $this->db->get($row['items_unit']."_blocks"))
							{
								if ($result->num_rows() > 0)
								{
									$row = $result->row_array();
									System::$lazy['this_page']['info'] = $this->blocks->info($row['blocks_folder']);
								}
							}
						}
				
						return $this->debug->log("info_ok", OK);
					}
					else
					{
						_debug("Aliases Info Client");
					}
				}
				else
				{
					return $lazy_config = $this->errors->add("aliases_unit_get");
				}
			}
		}
		
		function get($items_id, $table, $unit = "", $langs_code = "")
		{
			$unit = ($unit == "") ? System::$lazy['this_unit'] : $unit;
			$langs_code = ($langs_code == "") ? Locals::$langs['this'] : $langs_code; 
			
			$_values['table'] = "aliases_items";
			$return = FALSE;
			
			$this->db->select('aliases_id');
			$this->db->where('items_id', $items_id);
			$this->db->where('items_table', $table);
			$this->db->where('items_unit', $unit);
			$this->db->limit(1);
		
			if ($result = $this->db->get($_values['table']))
			{
				if ($result->num_rows() > 0)
				{
					$row = $result->row_array();
					$_values['table'] = "aliases_texts";
					
					$this->db->select('items_name, langs_code');
					$this->db->where('items_id', $row['aliases_id']);
					$this->db->where('langs_code', $langs_code);
					if ($langs_code != Locals::$langs['default'][System::$type])
					{
						$this->db->or_where('langs_code', Locals::$langs['default'][System::$type]);
						$this->db->where('items_id', $row['aliases_id']);
					}
					
					if ($result = $this->db->get($_values['table']))
					{
						if ($result->num_rows() > 0)
						{
							foreach ($result->result_array() as $row)
							{
								$return = ( ! $return) ? $row['items_name'] : ($row['langs_code'] == $langs_code) ? $row['items_name'] : $return;
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
				else
				{
					$this->debug->log("empty", ERROR, $_values);
				}
			}
			else 
			{
				$this->debug->log("get", ERROR, $_values);
			}
			
			return $return;
		}
	
		function delete($post)
		{
			$this->db->where("items_table", $post['table']);
			$this->db->where("items_unit", $post['unit']);
			$this->db->where("items_id", $post['id']);
			
			if ($this->db->delete("aliases_items"))
			{
				return $this->debug->log("aliases_delete_ok", OK);
			}
			
			return $this->debug->log("aliases_delete_error", ERROR);
		}
	}