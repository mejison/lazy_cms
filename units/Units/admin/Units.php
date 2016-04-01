<?php

	class Units extends Unit
	{
		var $_unit = "";
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($info, $params = array())
		{
			if ($info['folder'] == 'units_add')
			{
				$this->fields->get($info);
				System::$lazy['cats_list'] = $this->cats_list();
			}
			
			if ($info['folder'] == 'units_show')
			{
				System::$lazy['units'] = $this->get();
			}

			System::$lazy['params'] = $params;
			
			return $this->files->block($info);
		}
		
		function edit($id)
		{
			$_values['table'] = $this->_unit."_items";
			
			$select = array("cats_id",
							"items_pos",
							"items_folder",
							"items_active",
							"items_menu",
							"items_default",
							"items_autoload",
							"items_delete",
							"items_level");
							
			$this->db->select(implode(", ", $select));
			$this->db->where("items_id", $id);
			$this->db->limit(1);
			
			if (count($row = $this->db->get($_values['table'])))
			{
				$_values['table'] = $this->_unit."_items_texts";
			
				$select = array("langs_code",
								"items_name");
								
				$this->db->select(implode(", ", $select));
				$this->db->where("items_id", $id);
				
				if (count($result = $this->db->get($_values['table'])))
				{
					foreach ($result as $texts)
					{
						$row['items_name'][$texts['langs_code']] = $texts['items_name'];
					}
				}

				return $row;
			}
		}
		
		function get()
		{
			$select = array('items_id as id',
							'cats_id',
							'items_pos',
							'items_folder',
							'items_active',
							'items_menu',
							'items_default',
							'items_autoload',
							'items_delete',
							'items_level',
							'items_name',
							'items_add',
							'items_edit');
			
			$this->db->select(implode(", ", $select));
			$_values['table'] = $this->_unit."_items";
			if (count($row = $this->db->get($_values['table'])))
			{
				$_values['table'] = $this->_unit."_items_texts";
			
				$select = array("langs_code",
								"items_name");
								
				foreach ($row as $key => $val)
				{
					$row[$key]['items_name'] = array(System::$lazy['_locals']['default']['admin'] => $row[$key]['items_name']); 
					$this->db->select(implode(", ", $select));
					$this->db->where("items_id", $val['id']);
					
					if (count($result = $this->db->get($_values['table'])))
					{
						foreach ($result as $texts)
						{
							$row[$key]['items_name'][$texts['langs_code']] = $texts['items_name'];
						}
						
						
					}
				}

				$this->debug->add("items_ok");
				return $row;
			}
			else
			{
				$this->debug->add("items_empty", WARNING);
			}
		}
		
		function cats_list()
		{
			$select = array('cats_id as id',
							'cats_name as name');
			
			$this->db->select(implode(", ", $select));
			$_values['table'] = $this->_unit."_cats";
			if (count($row = $this->db->get($_values['table'])))
			{
				$items = array();
				foreach ($row as $val)
				{
					$items[$val['id']] = $val['name'];
				}
				
				$this->debug->add("list_ok");
				return $items;
			}
			else
			{
				$this->debug->add("items_empty", WARNING);
			}
		}

		function save($post)
		{
			if ($post['id'] = $this->fields->insert_items($post, $this->_unit, "admin"))
			{
				if ($this->fields->insert_texts($post, $this->_unit."_items_texts"))
				{
					return $this->debug->add("update_ok");
				}
				else
				{
					return $this->debug->add("update_error", ERROR);
				}
			}
			else
			{
				return $this->debug->add("update_error", ERROR);
			}
		}
	}