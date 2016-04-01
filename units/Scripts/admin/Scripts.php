<?php

	class Scripts extends Unit
	{
		var $_unit = "";
		var $_config = array();
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
			$this->_config = isset(System::$lazy['_config'][ucfirst($this->_unit)]['cfg']) ? System::$lazy['_config'][ucfirst($this->_unit)]['cfg'] : array();
		}
		
		function output($info, $params = array())
		{
			$this->load();
			return $this->files->block($info);
		}

		function load()
		{
			if ( ! isset(System::$lazy['js']))
			{
				System::$lazy['js'] = array();
			}

			if (isset($this->_config['scripts_cache']) && $this->_config['scripts_cache'])
			{
				if ( ! ($cache = $this->is_cache(System::$lazy['js'])))
				{
					if ($cache = $this->set_cache(System::$lazy['js']))
					{
						System::$lazy['js'] = array($cache);
					}
				}
				else
				{
					System::$lazy['js'] = array($cache);
				}
			}
			
			System::$lazy['js'] = array_map("Files::replace_root", System::$lazy['js']);
		}
				
		function is_cache($files)
		{
			if ($name = $this->cache_name($files))
			{
				if (file_exists($path = UNITS.ucfirst($this->_unit)."/cache/".TYPE."/".$name))
				{
					return $path;
				}
			}

			$this->clear_cache($name);
			return FALSE;
		}
		
		function cache_name($files)
		{
			$list = array();
			$mod = 0;
			for ($i = 0, $count = count($files); $i < $count; $i++)
			{
				$part = explode("/", $files[$i]);
				$list[] = str_replace(".js", "", end($part));
				$mod = max($mod, filemtime($files[$i]));
			}
			
			if (count($list) > 0)
			{
				asort($list);
				return md5(implode("", $list))."_v".$mod.".js";
			}

			return FALSE;
		}
		
		function set_cache($files)
		{
			ob_start();
			for ($i = 0, $count = count($files); $i < $count; $i++)
			{
				if (file_exists($files[$i]))
				{
					include_once ($files[$i]);
				}
			}
			$result = ob_get_contents();
			@ob_end_clean();
			
			$from = 0;
			$to = 0;
			$finish = FALSE;
			$cut = array();
			while ( ! $finish)
			{
				if (($pos = strpos($result, "/*", $from)) !== FALSE)
				{
					$pre = ord(substr($result, $pos - 1, 1));
					if ($pre == 9 || $pre == 10 || $pre == 32)
					{
						$from = $pos;
						if (($pos = strpos($result, "*/", $from + 2)) !== FALSE)
						{
							$to = $pos + 2;
							$cut[] = substr($result, $from, $to - $from);
							$from = $to;
						}
						else
						{
							$cut[] = substr($result, $from);
							$finish = TRUE;
						}
					}
					else
					{
						$from = $pos + 2;
					}
				}
				else
				{
					$finish = TRUE;
				}
			}
			
			$from = 0;
			$to = 0;
			$finish = FALSE;
			while ( ! $finish)
			{
				$pos = strpos($result, "//", $from);
				if ($pos !== FALSE)
				{
					$pre = ord(substr($result, $pos - 1, 1));
					if ($pre == 9 || $pre == 10 || $pre == 32)
					{
						$from = $pos;
						$pos = strpos($result, "\n", $from + 2);
						if ($pos !== FALSE)
						{
							$to = $pos + 2;
							$cut[] = substr($result, $from, $to - $from);
							$from = $to;
						}
						else
						{
							$cut[] = substr($result, $from);
							$finish = TRUE;
						}
					}
					else
					{
						$from = $pos + 2;
					}
				}
				else
				{
					$finish = TRUE;
				}
			}
			
			foreach ($cut as $comments)
			{
				$buffer = str_replace($comments, "", $buffer);
			}
			
			$search = array("\n", "\t", "\r", ", ", ": ", " :", " {", ",}", "; ", "= ", " =");
			$replace = array("", "", "", ",", ":", ":", "{", "}", ";", "=", "=");
			$buffer = str_replace($search, $replace, $buffer);

			if ($name = $this->cache_name($files))
			{
				if ( ! file_exists($path = UNITS.ucfirst($this->_unit)."/cache/".TYPE."/"))
				{
					if ( ! mkdir($path, 0777, TRUE))
					{
						return FALSE;
					}
				}
				$file_path = $path.$name;
				if (file_put_contents($file_path, $buffer))
				{
					return $file_path;
				}
			}
			
			return FALSE;
		}
		
		function clear_cache($name)
		{
			if (file_exists($path = UNITS.ucfirst($this->_unit)."/cache/".TYPE."/"))
			{
				if ($files = array_diff(scandir($path), array('.', '..')))
				{
					$part = explode("_", $name);
					foreach ($files as $file)
					{
						if (strpos($file, $part[0]) !== FALSE)
						{
							unlink($path.$file);
						}
						else
						{
							if (isset($this->_config['scripts_cache_file_time_to_delete']) && (time() - filemtime($path.$file)) > $this->_config['scripts_cache_file_time_to_delete'])
							{
								unlink($path.$file);
							}
						}
					}
				}
			}
		}
	}