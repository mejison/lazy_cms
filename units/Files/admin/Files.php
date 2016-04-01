<?php

	class Files extends Unit
	{
		function __construct()
		{
			parent::__construct();
		}
		
		static function replace_root($string)
		{
			return str_replace(ROOT, "/", $string);
		}
		
		function config($info)
		{
			if ($info['cfg'] != "")
			{
				$file = $info['cfg'].((strpos($info['cfg'], ".cfg") !== FALSE) ? "" : ".cfg");
				if (file_exists($path = UNITS.ucfirst($info['unit'])."/".TYPE."/".strtolower($info['folder'])."/".$file))
				{
					include_once ($path);
					return isset($_config) ? $_config : array();
				}
			}
			
			return array();
		}
		
		function config_for_units($unit)
		{
			if (file_exists($path = UNITS.ucfirst($unit)."/".TYPE."/_files/"))
			{
				if ($files = array_diff(scandir($path), array(".", "..")))
				{
					foreach ($files as $file)
					{
						if (strpos($file, "!") !== 0)
						{
							if (strpos(strtolower($file), ".cfg") !== FALSE)
							{
								include_once ($path.$file);
								return isset($_config) ? $_config : array();
							}
						}
					}
				}
			}
			
			return array();
		}
		
		function view($file)
		{
			$_values['file'] = $file.((strpos($file, ".php") !== FALSE) ? "" : ".php");
			if (file_exists($path = ROOT.ltrim(strtolower(System::$lazy['tpls']['views']), "/").$_values['file']))
			{
				extract(System::$lazy);
				
				ob_start();
				include ($path);
				$result = ob_get_contents();
				@ob_end_clean();

				return $result;
			}

			return "";
		}
		
		function block($info)
		{
			$result = "";
			$file = $info['php'].((strpos($info['php'], ".php") !== FALSE) ? "" : ".php");
			if (file_exists($path = UNITS.ucfirst($info['unit'])."/".TYPE."/".strtolower($info['folder'])."/".$file))
			{
				extract(System::$lazy);

				ob_start();
				include ($path);
				$result = ob_get_contents();
				@ob_end_clean();
			}

			return $result;
		}
		
		function by_path($path, $types = array())
		{
			if (file_exists($path))
			{
				if ($files = array_diff(scandir($path), array('.', '..')))
				{
					if ( ! count($types))
					{
						$types = array('php', 'css', 'js', 'lng', 'err', 'cfg');
					}
				
					foreach ($files as $file)
					{
						if (strpos($file, "!") !== 0)
						{
							foreach ($types as $ext)
							{
								System::$lazy[$ext] = isset(System::$lazy[$ext]) ? System::$lazy[$ext] : array();
								
								if (strpos(strtolower($file), ".".strtolower($ext)) !== FALSE)
								{
									if ($ext == "css" && strpos($file, "@") === 0)
									{
										System::$lazy['print_css'] = isset(System::$lazy['print_css']) ? System::$lazy['print_css'] : array();
										System::$lazy['print_css'][] = $path.strtolower($file);
									}
									else
									{
										System::$lazy[$ext][] = $path.strtolower($file);
									}
								}
							}
						}
					}
				}
			}
		}

		function blocks_check($info)
		{
			if (file_exists($path = UNITS.ucfirst($info['unit'])."/".TYPE."/".strtolower($info['folder'])."/".strtolower($info['php']).".php"))
			{
				extract(System::$lazy);
				
				ob_start();
				include ($path);
				@ob_end_clean();
			}
		}

		function for_units($info, $types = array())
		{
			$res['php'] = "";
			$res['cfg'] = "";
			
			if (file_exists($path = UNITS.ucfirst($info['unit'])."/".TYPE."/".strtolower($info['folder'])."/"))
			{
				if ( ! count($types))
				{
					$types = array('php', 'css', 'js', 'lng', 'err', 'cfg');
				}

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
									if ($ext == "php" || $ext == "cfg")
									{
										$res[$ext] = str_replace(".".$ext, "", strtolower($file));
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
											System::$lazy[$ext][] = $path.strtolower($file);
										}
									}
								}
							}
						}
					}
				}
			}

			return $res;
		}
		
		function delete_folder($path)
		{
			if ($items = array_diff(scandir($path), array('.', '..')))
			{
				foreach($items as $item)
				{
					is_dir($item) ? $this->delete_folder($path.$item) : unlink($path.$item);
				}
			}
			rmdir($path);
		}
		
		
		
		
		
		
		
		
		function write($unit, $items_id, $table, $content, $filename)
		{
			$path = ROOT."/texts/".$unit."/".$table."/".$items_id."/";
			if ( ! file_exists($path))
			{
				if( ! mkdir($path, 0777, TRUE))
				{
					return $this->debug->log("write_dir_error", ERROR);
				}
			}
				
			if (is_array($content))
			{
				$return = 1;
				foreach (Locals::$langs['client'] as $lang)
				{
					$new_filename = str_replace('.php', "_".$lang.'.php', $filename);
					$text = (isset($content[$lang])) ? $content[$lang] : "";
					
					if ( ! file_put_contents($path.$new_filename, $text))
					{
						$return *= $this->debug->log("write_dir_error", ERROR);
					}
					else
					{
						$return *= $this->debug->log("write_dir_error", OK);
					}
				}
				
				return $return;
			}
			else
			{
				if ( ! file_put_contents($path.$filename, $content))
				{
					return $this->debug->log("write_dir_error", ERROR);
				}
				else
				{
					return $this->debug->log("write_dir_error", OK);
				}
			}
		}
		
		function edit($unit, $items_id, $table, $filename, $field)
		{
			$return = array();
			
			$path = ROOT."/texts/".$unit."/".$table."/".$items_id."/";
			
			foreach (Locals::$langs['client'] as $lang)
			{
				$new_filename = str_replace('.php', "_".$lang.'.php', $filename);
				if (file_exists($path.$new_filename))
				{
					if ($file = fopen($path.$new_filename, "r"))
					{
						if (filesize($path.$new_filename) > 0)
						{
							$return[$field.'-'.$lang] = fread($file, filesize($path.$new_filename));
							fclose($file);
						}
					}
				}
			}
			
			return $return;
		}
		
		function texts($file, $items_id, $table, $object = "", $langs_code = "")
		{
			$object = ($object == '') ? System::$lazy['this_page']['unit'] : $object;
			$langs_code = ($langs_code == "") ? Locals::$langs['this'] : $langs_code;
			
			$path = ROOT."/texts/".$object."/".$table."/".$items_id."/";
			$filename = str_replace(".", "_".$langs_code.".", $file);
			if ( ! file_exists($path.$filename))
			{
				if (isset(Locals::$langs['default'][System::$type]) && $langs_code != Locals::$langs['default'][System::$type])
				{
					$filename = str_replace(".", "_".Locals::$langs['default'][System::$type].".", $file);
					if ( ! file_exists($path.$filename))
					{
						$_values['file'] = $path.$filename;
						$this->debug->log("texts_empty", ERROR, $_values);
						return "";
					}
					else
					{
						$_values['file'] = $path.$filename;
						$this->debug->log("texts_ok", OK, $_values);
						return file_get_contents($path.$filename);
					}
				}
				else
				{
					$_values['file'] = $path.$filename;
					$this->debug->log("texts_empty", ERROR, $_values);
					return "";
				}
			}
			else
			{
				$text = file_get_contents($path.$filename);
				if (trim($text) == "")
				{
					if ($langs_code != Locals::$langs['default'][System::$type])
					{
						$filename = str_replace(".", "_".Locals::$langs['default'][System::$type].".", $file);
						if ( ! file_exists($path.$filename))
						{
							$_values['file'] = $path.$filename;
							$this->debug->log("texts_empty", ERROR, $_values);
							return "";
						}
						else
						{
							$_values['file'] = $path.$filename;
							$this->debug->log("texts_ok", OK, $_values);
							return file_get_contents($path.$filename);
						}
					}
					else
					{
						$_values['file'] = $path.$filename;
						$this->debug->log("texts_empty", ERROR, $_values);
						return "";
					}
				}
				else
				{
					$_values['file'] = $path.$filename;
					$this->debug->log("texts_ok", OK, $_values);
					return $text;
				}
			}
		}
		
		function delete($post)
		{
			if (delete_files(ROOT.'/texts/'.$post['unit'].'/'.$post['table'].'/'.$post['id'].'/', TRUE))
			{
				if (rmdir(ROOT.'/texts/'.$post['unit'].'/'.$post['table'].'/'.$post['id']))
				{
					return $this->debug->log("files_delete_all_ok", OK);
				}
			}
			
			return $this->debug->log("files_delete_error", ERROR);
		}
		
		
		
		

		function read($path)
		{
			if (file_exists($path))
			{
				extract(System::$lazy);

				ob_start();
				include ($path);
				$buffer = ob_get_contents();
				@ob_end_clean();

				$this->errors->add("files_read_ok", OK);
				return $buffer;
			}
			else
			{
				$this->errors->add("files_path_empty", WARNING);
				return "";
			}
		}
		
		function write_items($items_id, $texts, $unit = "", $langs_array = array())
		{
			$unit = ($unit == "") ? System::$lazy['this_unit'] : $unit;
			$langs_array = (count($langs_array) > 0) ? $langs_array : System::$lazy['langs_client'];
			
			$this->db->select("sort_id, items_text, langs_code");
			$this->db->where("items_id", $items_id);
			
			if ($result = $this->db->get($unit."_sort"))
			{
				if ($result->num_rows() > 0)
				{
					foreach ($result->result_array() as $row)
					{
						if (isset($texts[$row['langs_code']]))
						{
							if ($file = $this->write($texts[$row['langs_code']], $row['items_text'], $items_id, $unit))
							{
								$data_array = array("items_text" => $file);
								$this->db->where("sort_id", $row['sort_array']);
								if ($this->db->update($unit."_sort", $data_array))
								{
									$this->errors->add("white_items_ok", OK);
								}
								else
								{
									$this->errors->add("white_items_update");
								}
							}
						}
						else
						{
							$this->db->where("sort_id", $row['sort_array']);
							if ($this->db->delete($unit."_sort"))
							{
								$this->errors->add("white_items_delete_ok", OK);
							}
							else
							{
								$this->errors->add("white_items_delete_error");
							}
						}
					}
				}
				else
				{
					foreach ($langs_array as $lang)
					{
						if (isset($texts[$lang]))
						{
							if ($file = $this->write($texts[$lang], "", $items_id, $unit))
							{
								$data_array = array("items_id" => $items_id,
													"items_text" => $file,
													"langs_code" => $lang);
								
								if ($this->db->insert($unit."_sort", $data_array))
								{
									$this->errors->add("white_items_ok", OK);
								}
								else
								{
									$this->errors->add("white_items_update");
								}
							}
						}
					}
				}
			}
			else
			{
				return $this->errors->add("write_items_get");
			}
		}
		
		function delete_file_model($lazy_config, $unit, $filename)
		{
			if($filename != "")
			{
				$start_path = (($lazy_config['lazy_type'] == "admin") ? ".." : ".");
				$folder = "/units/".$unit."/text/";
				
				if (file_exists($start_path.$folder.$filename))
				{
					if (unlink($start_path.$folder.$filename))
					{
						$lazy_config = $this->errors->add($lazy_config, "files_delete_ok", TRUE, array($start_path.$folder.$filename));
					}
					else
					{
						$lazy_config = $this->errors->add($lazy_config, "files_delete_false", TRUE, array($start_path.$folder.$filename));
					}
				}
				else
				{
					$lazy_config = $this->errors->add($lazy_config, "files_delete_empty_in_folder", TRUE, array($start_path.$folder.$filename));
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "files_delete_empty", TRUE);
			}
			
			return $lazy_config;
		}
		
		function delete_folder_model($lazy_config, $path)
		{
			if (file_exists($path))
			{
				if (is_dir($path))
				{
					$dir_handle = opendir($path);
					while (false !== ($file = readdir($dir_handle))) 
					{
						if ($file != '.' && $file != '..')
						{
							$temp_path = $path.'/'.$file;
							chmod($temp_path, 0777);
							
							if (is_dir($temp_path))
							{
								$lazy_config = $this->delete_folder_model($lazy_config, $temp_path);
							} 
							else 
							{ 
								if (file_exists($temp_path))
								{
									if (unlink($temp_path))
									{
										$lazy_config = $this->errors->add($lazy_config, "delete_folder_file_ok", TRUE, array($temp_path));
									}
									else
									{
										$lazy_config = $this->errors->add($lazy_config, "delete_folder_file_error", FALSE, array($temp_path));
									}
								}
								else
								{
									$lazy_config = $this->errors->add($lazy_config, "delete_folder_file_empty", FALSE, array($temp_path));
								}
							}
						}
					}
					
					closedir($dir_handle);
					
					if (rmdir($path))
					{
						$lazy_config = $this->errors->add($lazy_config, "delete_folder_ok", TRUE, array($path));
					}
					else
					{
						$lazy_config = $this->errors->add($lazy_config, "delete_folder_error", FALSE, array($path));
					}
				}
				else
				{
					if (unlink($path))
					{
						$lazy_config = $this->errors->add($lazy_config, "delete_folder_file_ok", TRUE, array($path));
					}
					else
					{
						$lazy_config = $this->errors->add($lazy_config, "delete_folder_file_error", FALSE, array($path));
					}
				}
			}
			else
			{
				$lazy_config = $this->errors->add($lazy_config, "delete_folder_empty", FALSE, array($path));
			}
			
			return $lazy_config;
		}
	}