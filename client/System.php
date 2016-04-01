<?php

	class System extends Lazy
	{
		static $lazy = array();
		function __construct()
		{
			parent::__construct();
			$this->init();
		}
		
		function init()
		{
			self::$lazy['_type'] = TYPE;
			
			self::$lazy['ajax'] = FALSE;
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			{
				self::$lazy['ajax'] = TRUE;
			}
			
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
				$aliases = $this->langs->check();
				$this->aliases->info($aliases);
				if ($this->uri->segment(1) == 'sw')
				{
					$this->db->get_lang('access_fields');
					_debug($this->db->last);
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