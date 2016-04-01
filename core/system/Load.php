<?php

	class Load
	{
		protected $_ob_level;
		protected $_loaded;
		protected $_ci_loaded_files	= array();
		protected $_ci_models = array();
		protected $_ci_helpers = array();
		protected $_ci_varmap = array('unit_test' => 'unit', 
									  'user_agent' => 'agent');

		public function __construct()
		{
			$this->_ob_level = ob_get_level();
		}

		public function set_loaded()
		{
			$this->_loaded = loaded();
			foreach ($this->_loaded as $unit)
			{
				$this->_load_files(ucfirst($unit));
			}
		}

		public function library($library = "")
		{
			$library = strtolower($library);
			if ($library == "")
			{
				return FALSE;
			}
			
			if (isset($this->_loaded[$library]))
			{
				return TRUE;
			}

			return $this->_load(ucfirst($library));
		}

		public function unit($unit = "")
		{
			$unit = strtolower($unit);
			if ($unit == "")
			{
				return FALSE;
			}
			
			if (isset($this->_loaded[$unit]))
			{
				return TRUE;
			}

			$this->_load_files(ucfirst($unit));
			return $this->_load(ucfirst($unit), TRUE);
		}
		
		function _load_files($unit)
		{
			if (file_exists($path = UNITS.ucfirst($unit)."/".TYPE."/_files/"))
			{
				$types = array('cfg', 'js', 'css', 'lng', 'err');
				if ($files = array_diff(scandir($path), array('.', '..')))
				{
					foreach ($files as $file)
					{
						if (strpos($file, "!") !== 0)
						{
							foreach ($types as $ext)
							{
								if (strpos(strtolower($file), ".".$ext) !== FALSE)
								{
									if ($ext == "cfg")
									{
										include_once ($path.$file);
										$cfg = isset($_config) ? $_config : array();
										if ($cfg)
										{
											System::$lazy['_config'][$unit]['cfg'] = $cfg;
											unset($cfg);
										}
									}
									else
									{
										System::$lazy[$ext] = isset(System::$lazy[$ext]) ? System::$lazy[$ext] : array();
										if ($ext == "css" && strpos($file, "@") === 0)
										{
											System::$lazy['print_css'] = isset(System::$lazy['print_css']) ? System::$lazy['print_css'] : array();
											System::$lazy['print_css'][] = $path.strtolower($file);
										}
										else
										{
											System::$lazy[$ext][] = $path.$file;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		protected function _load($class, $is_unit = FALSE)
		{
			$path = ($is_unit) ? UNITS.$class."/".TYPE."/".$class.".php" : CORE."libraries/".$class.".php";
			if (file_exists($path))
			{
				if ($is_unit && ! class_exists('Unit'))
				{
					load('Unit', 'system');
				}
			
				include_once($path);
			}
			
			if ( ! class_exists($class))
			{
				exit("Class ".$class." doesn't exist");
			}

			$var = strtolower($class);
			$this->_loaded[$var] = $class;
			
			$Lazy =& get_instance();
			$Lazy->$var = new $class;
			
			return TRUE;
		}

		public function dbutil()
		{
			if ( ! class_exists('CI_DB'))
			{
				$this->database();
			}

			$CI =& get_instance();
			$CI->load->dbforge();

			require_once(BASEPATH.'database/DB_utility.php');
			require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_utility.php');
			$class = 'CI_DB_'.$CI->db->dbdriver.'_utility';

			$CI->dbutil = new $class();
		}

		public function dbforge()
		{
			if ( ! class_exists('CI_DB'))
			{
				$this->database();
			}

			$CI =& get_instance();

			require_once(BASEPATH.'database/DB_forge.php');
			require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_forge.php');
			$class = 'CI_DB_'.$CI->db->dbdriver.'_forge';

			$CI->dbforge = new $class();
		}

		protected function _ci_object_to_array($object)
		{
			return (is_object($object)) ? get_object_vars($object) : $object;
		}

		protected function &_ci_get_component($component)
		{
			$CI =& get_instance();
			return $CI->$component;
		}

		protected function _ci_prep_filename($filename, $extension)
		{
			if ( ! is_array($filename))
			{
				return array(strtolower(str_replace('.php', '', str_replace($extension, '', $filename)).$extension));
			}
			else
			{
				foreach ($filename as $key => $val)
				{
					$filename[$key] = strtolower(str_replace('.php', '', str_replace($extension, '', $val)).$extension);
				}

				return $filename;
			}
		}
	}