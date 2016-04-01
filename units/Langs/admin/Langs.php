<?php
	
	class Langs extends Unit
	{
		var $_unit = "";
		static $_loaded = array();
		static $_langs = array();
		static $_config = array();
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
			$this->init();
		}
		
		function output($info, $params = array())
		{
			System::$lazy['units_list'] = $this->get_units();
			return $this->files->block($info, $this->unit);
		}
		
		function init()
		{
			$select = array("items_id as id",
							"items_type as type",
							"items_name as name",
							"items_code as code",
							"items_default as def");
			$this->db->select(implode(", ", $select));
			$this->db->order_by("items_pos", "asc");
			$this->db->where("items_active", TRUE);
			$this->db->where("items_delete", FALSE);

			$result = $this->db->get("langs_items");
			if (count($result))
			{
				foreach ($result as $row)
				{
					self::$_config[$row['type']][] = $row;
					if ($row['def'])
					{
						self::$_config['default'][$row['type']] = $row['code'];
					}
				}
				
				$this->debug->add("ok_langs");
			}
			else
			{
				$this->debug->close("empty_langs", ERROR);
			}
		}
		
		function check()
		{
			self::$_config['this'] = $this->uri->segment(1);
			if (self::$_config['this'] == "")
			{
				if ( ! isset(self::$_config['default'][TYPE]))
				{
					$this->debug->close('empty_default_langs', ERROR);
				}
				
				self::$_config['this'] = self::$_config['default'][TYPE];
			}
			else
			{
				$this->db->where("items_type", TYPE);
				$this->db->where("items_code", self::$_config['this']);
				$this->db->where("items_active", TRUE);
				$this->db->limit(1);
				
				if ($this->db->count_all('langs_items') > 0)
				{
					$this->debug->add("ok_langs_check");
				}
				else
				{
					$this->debug->not_found();
				}
			}
			
			return TRUE;
		}
		
		function load()
		{
			self::$_config['this'] = isset(self::$_config['this']) ? self::$_config['this'] : LANG;
			
			if (isset(System::$lazy['lng']) && is_array(System::$lazy['lng']))
			{
				foreach (System::$lazy['lng'] as $file)
				{
					if ( ! in_array($file, self::$_loaded))
					{
						self::$_loaded[] = $file;

						include_once ($file);
						self::$_langs = array_merge(self::$_langs, isset($_langs[self::$_config['this']]) ? $_langs[self::$_config['this']] : ((isset($_langs[self::$_config['default'][TYPE]])) ? $_langs[self::$_config['default'][TYPE]] : array()));
					}
				}
			}
			
			System::$lazy['_langs'] = self::$_langs;
			System::$lazy['_locals'] = self::$_config;
		}
		
		
		
		function get_units($langs_code = "")
		{
			$langs_code = ($langs_code == "") ? Locals::$langs['this'] : $langs_code;
			$items = array();
			
			$this->db->select("units_texts.items_name, units_id, units_folder");
			$this->db->where("units_texts.langs_code", $langs_code);
			$this->db->where("units_texts.items_table", "items");
			$this->db->join("units_texts", "units_items.units_id = units_texts.items_id");
			$result = $this->db->get("units_items");
			
			if (count($result))
			{
				foreach ($result as $row)
				{
					$units_array = array();

					$path = ROOT."/units/".ucfirst($row['units_folder'])."/zfiles/";
					$units_array['files'] = $this->files->by_path($path, array("lng"));

					if (count($units_array['files']) > 0)
					{
						$units_array['object'] = ucfirst($row['units_folder']);
						$units_array['name'] = $row['items_name'];
						$units_array['block'] = FALSE;
						$units_array['block_name'] = FALSE;
						$units_array['type'] = FALSE;
						
						$items[] = $units_array;
					}

					$this->db->select("blocks_id, blocks_type, blocks_folder");
					$this->db->where("blocks_active", TRUE);
					$this->db->where("blocks_type", "client");

					$res = $this->db->get($row['units_folder']."_blocks");
					
					if (count($res))
					{
						foreach ($res as $val)
						{
							$blocks_array = array();
							
							$path = ROOT."/units/".ucfirst($row['units_folder'])."/".$val['blocks_type']."/".$val['blocks_folder']."/";
							$blocks_array['files'] = $this->files->by_path($path, array("lng"));
							
							if (count($blocks_array['files']) > 0)
							{
								$blocks_array['object'] = ucfirst($row['units_folder']);
								$blocks_array['name'] = $row['items_name'];
								$blocks_array['block'] = $val['blocks_folder'];
								$blocks_array['block_name'] = $this->locals->text($row['units_folder'], $val['blocks_id'], "blocks", "items_name", Locals::$langs['default'][$val['blocks_type']]);
								$blocks_array['type'] = $val['blocks_type'];
								
								$items[] = $blocks_array;
							}
						}
					}
					
				}
			}
			
			
			return $items;
		}

		function get_adds()
		{
			$items = array();
			
			$path = ROOT."/adds/";
			$_values['path'] = $path;
			if ($path != "" && file_exists($path))
			{
				if ($files = scandir($path))
				{
					for ($i = 2; $i < count($files); $i++)
					{
						$check = FALSE;
						$row['items_name'] = $files[$i];
						
						$path = ROOT."/adds/".ucfirst($files[$i])."/zfiles/";
						$row['files'] = $this->files->by_path($path, array("lng"));
						if (count($row['files']) > 0)
						{
							$check = TRUE;
						}
						
						$row['blocks'] = array();
						
						$path = ROOT."/adds/".$files[$i]."/";
						$_values['path'] = $path;
						if ($path != "" && file_exists($path))
						{
							if ($blocks = scandir($path))
							{
								for ($j = 2; $j < count($blocks); $j++)
								{
									if (strpos($blocks[$j], ".php") === FALSE)
									{
										$val['items_name'] = $blocks[$j];
										
										$path = ROOT."/adds/".ucfirst($files[$i])."/".$blocks[$j]."/";
										$val['files'] = $this->files->by_path($path, array("lng"));
										
										if (count($val['files']) > 0)
										{
											$row['blocks'][] = $val;
											$check = TRUE;
										}
									}
								}
							}
						}
						
						if ($check)
						{
							$items[] = $row;
						}
					}
				}
				else
				{
					$this->debug->log("path_error", ERROR, $_values);
				}
			}
			else
			{
				$this->debug->log("path_empty", ERROR, $_values);
			}

			return $items;
		}
	
		function get_file($post)
		{
			$formed_langs = array();
			if (file_exists($post['file']))
			{
				include_once($post['file']);
				foreach ($langs as $type => $type_value)
				{
					foreach ($type_value as $lang => $lang_value)
					{
						foreach ($lang_value as $key => $value)
						{
							$formed_langs[$key][$type][$lang] = $value;
						}
					}
				}
			}
			
			$post['list'] = $formed_langs;
			
			return $post;
		}
		
		function save_file($post)
		{
			$file_content = '<?php'."\n\n";
			foreach ($post['content'] as $key => $key_value)
			{
				foreach ($key_value as $type => $type_value)
				{
					foreach ($type_value as $lang => $value)
					{
						$file_content .= "\t".'$langs'."[".(($type) ? "CLIENT" : "SERVER")."]['".$lang."']['".$key."']".' = "'.$value.'";'."\n";
					}
				}
			}
			
			if (file_put_contents($post['file'], $file_content))
			{
				return $this->debug->log("langs_ok", OK);
			}
			else
			{
				return $this->debug->log("langs_error");
			}
		}
	}
	
/* End of file langs_admin.php */