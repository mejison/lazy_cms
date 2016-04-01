<?php

	class Lazy
	{
		private static $instance;
		public function __construct()
		{
			self::$instance =& $this;
			foreach (loaded() as $var => $class)
			{
				$this->$var =& load($class);
			}

			$this->load =& load('Load', 'system');
			$this->load->set_loaded();
		}

		public static function &get_instance()
		{
			return self::$instance;
		}
	}