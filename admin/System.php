<?php

	class System extends Lazy
	{
		static $lazy = array();
		function __construct()
		{
			parent::__construct();
			$this->debug->begin('ajax');
			$this->init();
		}
		
		function init()
		{
			self::$lazy['_type'] = TYPE;
			self::$lazy['_path'] = "panel";
			self::$lazy['ajax'] = ($this->uri->segment(2) == 'ajax') ? TRUE : FALSE;
			
			$this->autoload();
		}

		function index()
		{
			if (self::$lazy['ajax'])
			{
				if ($this->langs->check())
				{
					$this->ajax->output($_POST);
				}
			}
			else
			{
				$unit = ($this->uri->segment(2)) ? $this->uri->segment(2) : "";
				if ($this->langs->check())
				{
					if ($this->aliases->check($unit))
					{
						if ( ! $this->access->logged_in())
						{
							$unit = "access";
						}
					}
				}

				if ($this->aliases->info($unit))
				{
					if ( ! $this->access->check())
					{
						$this->access->lock();
					}
				}
				
				$this->output();
			}
		}
		
		function output()
		{
			self::$lazy['_print'] = FALSE;
			
			$template = isset(self::$lazy['_info']['template']) ? self::$lazy['_info']['template'] : "template";
			$tpls_content = $this->files->view($template);

			$this->langs->load();
			$this->debug->load();
			
			self::$lazy['_print'] = TRUE;
			self::$lazy['php_time'] = $this->debug->end("temp", TRUE) + 5;
			$blocks_content = $this->blocks->_print();

			foreach ($blocks_content as $key => $val)
			{
				$tpls_content = str_replace($key, $val, $tpls_content);
			}
			
			$this->debug->set();
			echo $tpls_content;
		}
		
		function autoload()
		{
			$this->load->library("Sessions");
			
			$this->db->order_by("items_pos", "asc");
			$this->db->select("items_id, items_folder");
			$this->db->where("items_active", TRUE);
			$this->db->where("items_delete", FALSE);
			$this->db->where("items_autoload", TRUE);
			$result = $this->db->get("units_items");
			
			foreach ($result as $row)
			{
				$this->load->unit($row['items_folder']);
			}
		}
	}