<?php

	class Tpls extends Unit
	{
		var $_unit = "";
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
			$this->init();
		}
		
		function init()
		{
			$select = array("items_id as id",
							"items_folder as folder");
			$this->db->select(implode(", ", $select));
			$this->db->where("items_active", TRUE);
			$this->db->where("items_default", TRUE);
			$this->db->where("items_delete", FALSE);
			$this->db->where("items_type", TYPE);
			$this->db->limit(1);
			
			$_values['table'] = "tpls_items";
			$result = $this->db->get($_values['table']);
			if (count($result))
			{
				$row = $result;
				$_values['folder'] = $row['folder'];
				
				System::$lazy['tpls']['path'] = "/templates/".$row['folder']."/";
				if (file_exists(ROOT.System::$lazy['tpls']['path']))
				{
					System::$lazy['tpls']['id'] = $row['id'];
					System::$lazy['tpls']['folder'] = $row['folder'];
					System::$lazy['tpls']['views'] = System::$lazy['tpls']['path']."views/";
					System::$lazy['tpls']['css'] = System::$lazy['tpls']['path']."css/";
					System::$lazy['tpls']['images'] = System::$lazy['tpls']['path']."images/";
				}
				else
				{
					$this->debug->log("empty_tpls_folder", ERROR, $_values);
				}
			}
		}
	
		function output($info)
		{
			return $this->files->block($info['php'], $info['folder'], $this->unit);
		}
	}