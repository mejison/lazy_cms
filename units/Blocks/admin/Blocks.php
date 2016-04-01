<?php

	class Blocks extends Unit
	{
		var $_unit = "";
		static $_blocks_ids = array();
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($info, $params = array())
		{
			return $this->files->block($info);
		}
		
		function _print()
		{
			$blocks = array();
			if (isset(System::$lazy['blocks']))
			{
				foreach (System::$lazy['blocks'] as $info)
				{
					if (isset($info['unit']))
					{
						System::$lazy['this_block'] = $info['folder'];
						System::$lazy['this_id'] = $info['blocks_id'];
						
						$this->load->unit($info['unit']);
						$blocks[$info['unit']."::".$info['folder']] = $this->$info['unit']->output($info);
					}
				}
			}

			return $blocks;
		}

		function put($folder, $parent = 0, $params = array())
		{
			System::$lazy['blocks'] = isset(System::$lazy['blocks']) ? System::$lazy['blocks'] : array();
			$temp = explode("_", $folder);
			$unit = array_shift($temp);
			
			System::$lazy['this_block'] = $folder;
			$key = "";
			if ( ! System::$lazy['_print'])
			{
				System::$lazy['_tree'] = isset(System::$lazy['_tree']) ? System::$lazy['_tree'] : array();
		
				if ($info = $this->info($folder, $unit))
				{
					$info['blocks_id'] = $this->random_id();
					if ($info['folder'] == "menus_content")
					{
						if (isset(System::$lazy['_this']['info']) && System::$lazy['_this']['info'])
						{
							System::$lazy['_this']['info']['blocks_id'] = $info['blocks_id'];
							$folder = System::$lazy['_this']['info']['folder'];
							$this->files->blocks_check(System::$lazy['_this']['info']);
						}
					}
					else
					{
						$this->files->blocks_check($info);
					}
					
					if (isset($parent))
					{
						System::$lazy['_tree'][$parent][] = array('folder' => $folder,
																  'id' => $info['blocks_id']);
					}
					
					$key = $info['unit']."::".$info['folder'];
					System::$lazy['blocks'][$key] = $info;
				}
			}
			else
			{
				if ($parent > 0)
				{
					System::$lazy['_config'][$folder]['parent'] = $parent;
				}
				
				$this->load->unit($unit);
				return $this->$unit->output(System::$lazy['_config'][$folder], $params);
			}
			
			return $key;
		}
		
		function info($folder = "", $unit = "")
		{
			$unit = ($unit == "") ? System::$lazy['_this']['unit'] : $unit;
			
			if ($folder == "")
			{
				$this->db->where("blocks_default", TRUE);
			}
			else
			{
				$this->db->where("blocks_folder", $folder);
			}
			
			$this->db->select("blocks_id, blocks_folder");
			$this->db->where("blocks_type", TYPE);
			$this->db->where("blocks_active", TRUE);
			$this->db->where("blocks_logged_in ".(($this->access->logged_in()) ? ">=" : "<="), 1);
			$this->db->limit(1);
			
			$_values['table'] = $unit."_blocks";
			if (count($row = $this->db->get($_values['table'])))
			{
				$info = array();
				$info['unit'] = $unit;
				$info['folder'] = $row['blocks_folder'];
				$info['id'] = $row['blocks_id'];
				
				$info = array_merge($info, $this->files->for_units($info, $this->types($folder)));
				
				$info['cfg'] = $this->files->config($info);

				System::$lazy['_info'] = $info['cfg'];
				System::$lazy['_config'][$info['folder']] = $info;
				
				return $info;
			}
			else
			{
				$_values['folder'] = $folder;
				return $this->debug->add("empty_blocks_info", ERROR, $_values);
			}
		}
		
		function types($folder)
		{
			if (isset(System::$lazy['_tree']))
			{
				foreach (System::$lazy['_tree'] as $parents)
				{
					foreach ($parents as $blocks)
					{
						if ($blocks['folder'] == $folder)
						{
							return array('lng', 'err', 'php', 'cfg');
						}	
					}
				}
			}
			
			return array();
		}
		
		function print_block($folder, $unit)
		{
			$info = $this->info($folder, $unit);

			System::$lazy['_print'] = FALSE;
			
			$template = isset($info['cfg']['template']) ? $info['cfg']['template'] : "template";
			$tpls_content = $this->files->view($template);

			$this->langs->load();
			$this->debug->load();

			System::$lazy['_print'] = TRUE;
			System::$lazy['_content'] = $this->files->block($info);
			$blocks_content = $this->_print();

			foreach ($blocks_content as $key => $val)
			{
				$tpls_content = str_replace($key, $val, $tpls_content);
			}
			
			$this->debug->set();
			echo $tpls_content;
		}
		
		function get($post)
		{
			System::$lazy['js'] = array();
			System::$lazy['css'] = array();
			System::$lazy['_tree'] = (isset($post['tree'])) ? $post['tree'] : array();
			
			if ($this->info($post['block'], $post['unit']))
			{					
				System::$lazy['_config'][$post['block']]['blocks_id'] = System::$lazy['this_id'] = $this->blocks->random_id();
				System::$lazy['_tree'][0][] = array('folder' => $post['block'],
													'id' => System::$lazy['this_id']);

				System::$lazy['this_block'] = $post['block'];
				System::$lazy['_print'] = FALSE;
				$this->files->blocks_check(System::$lazy['_config'][$post['block']]);
				System::$lazy['_print'] = TRUE;
				
				$this->langs->load();

				System::$lazy['this_block'] = $post['block'];
				$this->load->unit($post['unit']);
				$page_content = $this->$post['unit']->output(System::$lazy['_config'][$post['block']]);
				
				$this->css->load();
				$this->scripts->load();
				$this->debug->load();
				
				$data_array = array('page' => $page_content,
									'block' => $post['block'],
									'tree' => System::$lazy['_tree'],
									'unit' => $post['unit'],
									'blocks_id' => System::$lazy['this_id'],
									'fields_config' => (isset(System::$lazy['_config']['fields'][System::$lazy['this_id']]['fields_config'])) ? System::$lazy['_config']['fields'][System::$lazy['this_id']]['fields_config'] : array(),
									'js' => System::$lazy['js'],
									'css' => System::$lazy['css'],
									'langs' => System::$lazy['_langs'],
									'errors' => System::$lazy['_errors'],
									'hints' => System::$lazy['_hints']);
	
				return $data_array;
			}
		}
		
		function random_id()
		{
			mt_srand();
			$id = "block_".rand(1000, 9999);
			
			while(in_array($id, self::$_blocks_ids))
			{
				$id = "block_".rand(1000, 9999);
			}
			
			self::$_blocks_ids[] = $id;
			return $id;
		}
	}