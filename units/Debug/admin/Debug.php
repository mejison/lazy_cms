<?php

	class Debug extends Unit
	{
		var $_unit = "";
		var $_begin = array();
		var $_end = array();
		
		static $_errors = array();
		static $_config = array();
		
		function __construct()
		{
			parent::__construct();
			
			global $time_temp;
			$this->_begin['temp'] = $time_temp;
			
			$this->_unit = strtolower(get_class($this));
		}
		
		function begin($mark = "temp")
		{
			$this->_begin[$mark] = microtime(TRUE);
		}
		
		function end($mark = "temp", $ms = FALSE)
		{
			$this->_end[$mark] = microtime(TRUE);
			return round($this->_end[$mark] - $this->_begin[$mark], 3) * (($ms) ? 1000 : 1);
		}
		
		function output($info, $params = array())
		{
			return $this->files->block($info, $this->unit);
		}
		
		function close($code, $type = OK, $values = array())
		{
			exit;
		}
		
		function not_found()
		{
			System::$lazy['_config'][ucfirst($this->_unit)]['cfg'] = $this->files->config_for_units($this->_unit);
			$cfg = System::$lazy['_config'][ucfirst($this->_unit)]['cfg'];
			if (isset($cfg['blocks_not_found']))
			{
				set_header(404);
				$this->blocks->print_block($cfg['blocks_not_found'], $this->_unit);
			}
			exit;
		}

		function load()
		{
			if (isset(System::$lazy['err']) && is_array(System::$lazy['err']))
			{
				foreach (System::$lazy['err'] as $file)
				{
					include_once ($file);
					if (isset($_errors))
					{
						foreach ($_errors as $mode => $mode_array)
						{
							foreach ($mode_array as $type => $type_array)
							{
								$errors_text = isset($type_array[Langs::$_config['this']]) ? $type_array[Langs::$_config['this']] : ((isset($type_array[Langs::$_config['default'][TYPE]])) ? $type_array[Langs::$_config['default'][TYPE]] : ((isset($type_array[LANG])) ? $type_array[LANG] : FALSE));
								if ($errors_text)
								{
									foreach ($errors_text as $key => $value)
									{
										$errors_type = ($mode == LOG) ? 'logs' : (($mode == HINT) ? 'hints' : 'errors');
										if ( ! isset(self::$_config[$errors_type]))
										{
											self::$_config[$errors_type] = array();
										}
										self::$_config[$errors_type][$key]['text'] = $value;
										self::$_config[$errors_type][$key]['type'] = $type;
									}
								}
							}
						}
					}
				}
			}
			
			System::$lazy['_errors'] = isset(self::$_config['errors']) ? self::$_config['errors'] : array();
			System::$lazy['_hints'] = isset(self::$_config['hints']) ? self::$_config['hints'] : array();
		}
		
		function add($key, $type = OK, $values = array())
		{
			$debug = debug_backtrace();
			$errors_items = array("time" => date("H").":".date('i').":".date('s'),
								  "key" => $key,
								  "type" => $type,
								  "values" => $values,
								  "func" => (isset($debug[1]['function'])) ? $debug[1]['function'] : "",
								  "class" => (isset($debug[1]['class'])) ? $debug[1]['class'] : "");
			self::$_errors[] = $errors_items;
			return ($type) ? TRUE : FALSE;
		}
		
		function set()
		{
			if (count(self::$_errors) > 0)
			{
				$errors = array();
				$logs = array();
				foreach (self::$_errors as $error)
				{
					if (isset(self::$_config['errors'][$error['key']]))
					{
						$error['text'] = self::$_config['errors'][$error['key']]['text'];
						foreach ($error['values'] as $key => $val)
						{
							$error['text'] = str_replace("[:".$key."]", $val, $error['text']);
						}
						
						$errors[] = $error;
					}
					
					if (isset(self::$_config['logs'][$error['key']]))
					{
						$error['text'] = self::$_config['logs'][$error['key']]['text'];
						foreach ($error['values'] as $key => $val)
						{
							$error['text'] = str_replace("[:".$key."]", $val, $error['text']);
							
							if ($key == "object")
							{
								$error['object'] =  $val;
							}
						}

						$logs[] = $error;
					}
				}

				if (isset(self::$_config['logs_enable']) && self::$_config['logs_enable'] && count($logs) > 0)
				{
					$this->save($logs);
				}
				
				System::$lazy['errors'] = self::$_errors = $errors;
			}
		}
		
		function save($logs)
		{
			if ( ! file_exists($path = ROOT."/logs/".TYPE."/".mktime(0, 0, 0, date('n'), date('j'), date('Y'))."-".date('d-m-Y')."/"))
			{
				if ( ! mkdir($path, 0777, TRUE))
				{
					return;
				}
				
				$this->clear_logs();
			}
			
			$name = (($this->access->logged_in()) ? Access::$user['id'] : 0).".log";
			$string = "";
			foreach ($logs as $log)
			{
				$string .= implode(";", $log)."\r\n";
			}
			file_put_contents($path.$name, $string."\r\n", FILE_APPEND);
		}
		
		function clear_logs()
		{
			if (isset(self::$_config['logs_lifetime']) && self::$_config['logs_lifetime'] > 0 && file_exists($path = ROOT."/logs/".TYPE."/"))
			{
				if ($folders = array_diff(scandir($path), array('.', '..')))
				{
					foreach ($folders as $folder)
					{
						$part = explode("-", $folder);
						if ((time() - $part[0]) > self::$_config['logs_lifetime'])
						{
							$this->files->delete_folder($path);
						}
					}
				}
			}
		}
		
		function php($level, $message, $filepath, $line)
		{
			$vars = array();
			$levels = array(2 => 'E_WARNING',
							8 => 'E_NOTICE',
							256 => 'E_USER_ERROR',
							512 => 'E_USER_WARNING',
							1024 => 'E_USER_NOTICE',
							4096 => 'E_RECOVERABLE_ERROR',
							8191 => 'E_ALL');
							
			$php_error = array('level' => $levels[$level],
							   'message' => $message,
							   'file' => str_replace(ROOT, "/", $filepath),
							   'line' => $line);
			$vars['_php_error'] = $php_error;
			
			if (file_exists($path = UNITS.ucfirst($this->_unit)."/".TYPE."/_files/"))
			{
				if ($files = array_diff(scandir($path), array(".", "..")))
				{
					foreach ($files as $file)
					{
						if (strpos($file, "!") !== 0)
						{
							if (strpos(strtolower($file), ".cfg") !== FALSE)
							{
								include ($path.$file);
								$_config = isset($_config) ? $_config : array();
								
								if (isset($_config['blocks_php_error']))
								{
									$res = array();
									
									if (file_exists($path = UNITS.ucfirst($this->_unit)."/".TYPE."/".strtolower($_config['blocks_php_error'])."/"))
									{
										$types = array('php', 'css', 'js', 'lng', 'err', 'cfg');
										if ($files = array_diff(scandir($path), array(".", "..")))
										{
											foreach ($files as $file)
											{
												if (strpos($file, "!") !== 0)
												{
													foreach ($types as $ext)
													{
														if (strpos(strtolower($file), ".".$ext) !== FALSE)
														{
															$res[$ext] = isset($res[$ext]) ? $res[$ext] : array();
															if ($ext == "css" && strpos($file, "@") !== 0 || $ext != "css")
															{
																$res[$ext][] = $path.strtolower($file);
															}
														}
													}
												}
											}
										}
									}

									if (isset($res['lng']))
									{
										$langs_this = (class_exists("Langs") && isset(Langs::$_config['this'])) ? Langs::$_config['this'] : LANG;
										foreach ($res['lng'] as $file)
										{
											include ($file);
											if (isset($_langs[$langs_this]))
											{
												foreach ($_langs[$langs_this] as $key => $value)
												{
													$vars['_langs'][$key] = $value;
												}
											}
										}
									}
									
									if (isset($res['php']))
									{
										foreach ($res['php'] as $file)
										{
											if (file_exists($file))
											{
												extract($vars);

												ob_start();
												include ($file);
												$result = ob_get_contents();
												@ob_end_clean();
											}

											echo $result;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		function db($error, $message, $query, $filepath, $line)
		{
			$vars = array();
			
			$db_error = array('error' => $error,
							  'message' => $message,
							  'query' => $query,
							  'file' => str_replace(ROOT, "/", $filepath),
							  'line' => $line);
			$vars['_db_error'] = $db_error;
			
			if (file_exists($path = UNITS.ucfirst($this->_unit)."/".TYPE."/_files/"))
			{
				if ($files = array_diff(scandir($path), array(".", "..")))
				{
					foreach ($files as $file)
					{
						if (strpos($file, "!") !== 0)
						{
							if (strpos(strtolower($file), ".cfg") !== FALSE)
							{
								include ($path.$file);
								$_config = isset($_config) ? $_config : array();
								
								if (isset($_config['blocks_db_error']))
								{
									$res = array();
									
									if (file_exists($path = UNITS.ucfirst($this->_unit)."/".TYPE."/".strtolower($_config['blocks_db_error'])."/"))
									{
										$types = array('php', 'css', 'js', 'lng', 'err', 'cfg');
										if ($files = array_diff(scandir($path), array(".", "..")))
										{
											foreach ($files as $file)
											{
												if (strpos($file, "!") !== 0)
												{
													foreach ($types as $ext)
													{
														if (strpos(strtolower($file), ".".$ext) !== FALSE)
														{
															$res[$ext] = isset($res[$ext]) ? $res[$ext] : array();
															if ($ext == "css" && strpos($file, "@") !== 0 || $ext != "css")
															{
																$res[$ext][] = $path.strtolower($file);
															}
														}
													}
												}
											}
										}
									}

									if (isset($res['lng']))
									{
										$langs_this = (class_exists("Langs") && isset(Langs::$_config['this'])) ? Langs::$_config['this'] : LANG;
										foreach ($res['lng'] as $file)
										{
											include ($file);
											if (isset($_langs[$langs_this]))
											{
												foreach ($_langs[$langs_this] as $key => $value)
												{
													$vars['_langs'][$key] = $value;
												}
											}
										}
									}
									
									if (isset($res['php']))
									{
										foreach ($res['php'] as $file)
										{
											if (file_exists($file))
											{
												extract($vars);

												ob_start();
												include ($file);
												$result = ob_get_contents();
												@ob_end_clean();
											}

											echo $result;
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}