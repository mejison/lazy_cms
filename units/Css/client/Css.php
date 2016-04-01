<?php

	class Css extends Unit
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
			if ( ! isset(System::$lazy['css']))
			{
				System::$lazy['css'] = array();
			}
			
			if ( ! isset(System::$lazy['print_css']))
			{
				System::$lazy['print_css'] = array();
			}
			
			$this->files->by_path(ROOT.ltrim(System::$lazy['tpls']['css'], "/"), array('css'));
			if (isset($this->_config['css_cache']) && $this->_config['css_cache'])
			{
				if ( ! ($cache = $this->is_cache(System::$lazy['css'])))
				{
					if ($cache = $this->set_cache(System::$lazy['css']))
					{
						System::$lazy['css'] = array($cache);
					}
				}
				else
				{
					System::$lazy['css'] = array($cache);
				}
				
				if ( ! ($cache = $this->is_cache(System::$lazy['print_css'])))
				{
					if ($cache = $this->set_cache(System::$lazy['print_css']))
					{
						System::$lazy['print_css'] = array($cache);
					}
				}
				else
				{
					System::$lazy['print_css'] = array($cache);
				}
			}

			System::$lazy['css'] = array_map("Files::replace_root", System::$lazy['css']);
			System::$lazy['print_css'] = array_map("Files::replace_root", System::$lazy['print_css']);
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
				$list[] = str_replace(".css", "", end($part));
				$mod = max($mod, filemtime($files[$i]));
			}

			if (count($list) > 0)
			{
				asort($list);
				return md5(implode("", $list))."_v".$mod.".css";
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
					$finish = TRUE;
				}
			}
			
			foreach ($cut as $comments)
			{
				$result = str_replace($comments, "", $result);
			}
			
			$search = array("\n", "\t", "\r", ", ", ": ", " {", ";}" , "; ", "../images/");
			$replace = array("", "", "", ",", ":", "{", "}", ";", System::$lazy['tpls']['images']);
			$result = str_replace($search, $replace, $result); 

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
				if (file_put_contents($file_path, $result))
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
							if (isset($this->_config['css_cache_file_time_to_delete']) && (time() - filemtime($path.$file)) > $this->_config['css_cache_file_time_to_delete'])
							{
								unlink($path.$file);
							}
						}
					}
				}
			}
		}
	}