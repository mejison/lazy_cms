<?php

	class Fields extends Unit
	{
		var $_unit = "";
		
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($info, $params = array())
		{
			return $this->files->block($info);
		}
		
		function get($info)
		{
			$info['fields_config'] = array();
			$_values['table'] = $info['unit']."_fields";
			
			$select = array($_values['table'].".".$this->_unit."_id as id",
							$_values['table']."_texts.".$this->_unit."_name as name",
							$_values['table']."_texts.".$this->_unit."_placeholder as placeholder",
							$_values['table']."_texts.".$this->_unit."_info as info",
							$this->_unit."_active as active",
							$this->_unit."_code as code",
							$this->_unit."_max as `max`",
							$this->_unit."_min as `min`",
							$this->_unit."_patern as patern",
							$this->_unit."_require as `require`",
							$this->_unit."_langs as `langs`",
							$this->_unit."_type as type");
			$this->db->select(implode(", ", $select));
			$this->db->where("fields_active", TRUE);
			$this->db->order_by_field($_values['table'].'_texts.langs_code' , array(Langs::$_config['this'], Langs::$_config['default'][TYPE], LANG), 'ASC');
			$this->db->join($_values['table'].'_texts', $_values['table'].'_texts.fields_id = '.$_values['table'].'.fields_id');
			
			$result = $this->db->get($_values['table']);
		
			if (count($result))
			{
				foreach ($result as $row)
				{
					$info['fields_config'][$row['code']] = $row;
				}
				
				if (isset($info['blocks_id']))
				{
					System::$lazy['_config']['fields'][$info['blocks_id']] = $info;
				}
			}
			
			return $info;
		}
		
		function put($code, $blocks_id, $list = array())
		{
			$field = "";
			if (isset(System::$lazy['_config']['fields'][$blocks_id]['fields_config'][$code]) && System::$lazy['_config']['fields'][$blocks_id]['fields_config'][$code]['active'])
			{
				$config = System::$lazy['_config']['fields'][$blocks_id]['fields_config'][$code];
				$config['list'] = $list;
				$field = $this->$config['type']($config);
			}

			return $field;
		}

		function insert_items($data, $unit, $lang = "client")
		{
			$this->langs->load();
			$info = $this->get(array('unit' => $unit));
			
			$validate = TRUE;
			foreach ($data as $field => $val)
			{
				if (isset($info['fields_config'][$field]))
				{
					$validate *= $this->validate($val, $info['fields_config'][$field], $lang);
				}
			}
			
			if ($validate)
			{
				$_values['table'] = $unit."_items";
				$data_array = array();
				$fields = $this->db->fields_list($_values['table']);
				foreach ($fields as $field)
				{
					if (isset($data[$field]))
					{
						if (is_array($data[$field]))
						{
							$data_array[$field] = $data[$field][System::$lazy['_locals']['default'][$lang]];
						}
						else
						{
							$data_array[$field] = $data[$field];
						}
					}
				}
				
				if ($data['id'])
				{
					$data_array['items_edit'] = time();
					$this->db->where('items_id', $data['id']);
					if ($this->db->update($_values['table'], $data_array))
					{
						return $data['id'];
					}
					else 
					{
						return FALSE;
					}
				}
				else 
				{
					$data_array['items_id'] = $data['id'];
					$data_array['items_add'] = time();
					if ($this->db->insert($_values['table'], $data_array))
					{
						return $this->db->insert_id();
					}
					else 
					{
						return FALSE;
					}
				}
			}
			else
			{
				return FALSE;
			}
		}
		
		function insert_texts($data, $table)
		{
			$_values['table'] = $table;
			$data_array = array();
			$error = 1;
			$fields = $this->db->fields_list($_values['table']);
			foreach ($fields as $field)
			{
				if (isset($data[$field]))
				{
					foreach ($data[$field] as $k => $v)
					{
						$data_array = array();
						$data_array[$field] = $v;
						$data_array['langs_code'] = $k;
						$data_array['items_id'] = $data['id'];

						$this->db->where('items_id', $data['id']);
						$this->db->where('langs_code', $k);
					
						if ($this->db->count_all($_values['table']))
						{
							$this->db->where('items_id', $data['id']);
							$this->db->where('langs_code', $k);
							
							if ( ! $this->db->update($_values['table'], $data_array))
							{
								$error = 0;
							}
						}
						else 
						{	
							if ( ! $this->db->insert($_values['table'], $data_array))
							{
								$error = 0;
							}
						}
					}
				}
			}
			
			if ($error)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

		function submit($text = "")
		{
			$code = "";
			$code .= "<a href='javascript:void(0)' class='button submit'>";
			$code .= "<span class='button_top'></span>";
			$code .= "<span class='button_content'>";
			$code .= ($text != "") ? $text : Langs::$_langs['s_save'];
			$code .= "</span>";
			$code .= "</a>";
			
			return $code;
		}
		
		function input($config)
		{
			$code = "";
			if ($config['langs'] == "client" || $config['langs'] == "admin")
			{				
				for ($i = 0; $i < count(System::$lazy['_locals'][$config['langs']]); $i++)
				{
					$config['this_lang'] = System::$lazy['_locals'][$config['langs']][$i]['code'];
					$code .= "<div class='langs_box' data-lang='".System::$lazy['_locals'][$config['langs']][$i]['code']."'>";
					$code .= "<p class='form_text'>";
					$code .= ($config['require']) ? "<b class='require'>!</b> " : "";
					$code .= "<span>".$config['name']." ".System::$lazy['_locals'][$config['langs']][$i]['name']."</span>";
					$code .= ":";
					$code .= "</p>";
					$code .= $this->input_print($config);
					$code .= "</div>";
				}
			}
			else
			{
				$code .= "<p class='form_text'>";
				$code .= ($config['require']) ? "<b class='require'>!</b> " : "";
				$code .= "<span>".$config['name']."</span>";
				$code .= ":";
				$code .= "</p>";
				$code .= $this->input_print($config);
			}
			
			return $code;
		}

		function input_print($config)
		{
			$code = "";
			$code .= "<div class='input_box'>";
			$code .= "<input type='text' class='input'".(isset($config['this_lang']) ? " data-lang='".$config['this_lang']."'" : "")." name='".$config['code']."' value='' />";
			$code .= "<p class='_placeholder'>";
			$code .= $config['placeholder'];
			$code .= "</p>";
			$code .= "</div>";
			
			return $code;
		}
		
		function password($config)
		{
			$code = "";
			$for_langs = false;
			if ($for_langs)// && ($count = count(Locals::$langs['client'])) > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$code .= "<div class='langs_box' data-lang='".Locals::$langs['client'][$i]."'>";
					$code .= "<p class='form_text'>";
					if (Locals::$langs['client'][$i] == Locals::$langs['default']['client'] && $config['fields_require'])
					{
						$code .= "<b class='require'>!</b> ";
					}
					$code .= "<span>".((isset(System::$lazy['langs'][$id])) ? System::$lazy['langs'][$id] : "langs->".$id)." [".Locals::$langs['client'][$i]."]</span>";
					$code .= ":";
					$code .= "</p>";
					$code .= _input_print($id."-".Locals::$langs['client'][$i], $classes);
					$code .= "</div>";
				}
			}
			else
			{
				$code .= "<p class='form_text'>";
				$code .= ($config['require']) ? "<b class='require'>!</b> " : "";
				$code .= "<span>".$config['name']."</span>";
				$code .= ":";
				$code .= "</p>";
				$code .= $this->password_print($config);
			}
			
			return $code;
		}

		function password_print($config)
		{
			$code = "";
			$code .= "<div class='input_box'>";
			$code .= "<input type='password' class='input' name='".$config['code']."' value='' />";
			$code .= "<p class='_placeholder'>";
			$code .= $config['placeholder'];
			$code .= "</p>";
			$code .= "</div>";
			
			return $code;
		}
	
		function checkbox($config)
		{
			$code = "";
			$code .= "<p class='form_text'>";
			$code .= $this->checkbox_print($config);
			$code .= "</p>";
			
			return $code;
		}
		
		function checkbox_print($config)
		{
			$code = "";
			$code .= "<label>";
			$code .= "<input type='checkbox' class='checkbox' name='".$config['code']."' value='' />";
			$code .= $config['name']."</label>";
			$code .= "<p class='_placeholder'>";
			$code .= $config['placeholder'];
			$code .= "</p>";

			return $code;
		}
		
		function select($config)
		{
			$code = "";
			$for_langs = false;
			if ($for_langs)// && ($count = count(Locals::$langs['client'])) > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$code .= "<div class='langs_box' data-lang='".Locals::$langs['client'][$i]."'>";
					$code .= "<p class='form_text'>";
					if (Locals::$langs['client'][$i] == Locals::$langs['default']['client'] && $config['fields_require'])
					{
						$code .= "<b class='require'>!</b> ";
					}
					$code .= "<span>".((isset(System::$lazy['langs'][$id])) ? System::$lazy['langs'][$id] : "langs->".$id)." [".Locals::$langs['client'][$i]."]</span>";
					$code .= ":";
					$code .= "</p>";
					$code .= _input_print($id."-".Locals::$langs['client'][$i], $classes);
					$code .= "</div>";
				}
			}
			else
			{
				$code .= "<p class='form_text'>";
				$code .= ($config['require']) ? "<b class='require'>!</b> " : "";
				$code .= "<span>".$config['name']."</span>";
				$code .= ":";
				$code .= "</p>";
				$code .= $this->select_print($config);
			}
			
			return $code;
		}

		function select_print($config)
		{
			$code = "";
			$code .= "<div class='input_box'>";
			$code .= "<select class='list' name='".$config['code']."' />";
			$code .= "<option value=''>".$config['placeholder']."</option>";
			foreach ($config['list'] as $id => $name)
			{
				$code .= "<option value='".$id."'>".$name."</option>";
			}
			
			$code .= "</select>";
			$code .= "</div>";
			
			return $code;
		}

		function validate($value, $config, $lang)
		{
			$type = ($config['patern'] != "") ? $config['patern'] : $config['type'];
			if ( ! is_array($value))
			{
				$value = trim($value);
			}
			else
			{
				foreach ($value as $k => $v)
				{
					$value[$k] = trim($v);
				}
			}
			
			$type = ($type == "checkbox") ? $type."es" : $type;
			return ($type != "" && function_exists($type)) ? $this->$type($value, $config, $lang) : $this->text($value, $config, $lang);
		}
		
		function text($value, $config, $lang)
		{
			$error = TRUE;
			if ($error = $this->requires($value, $config, $lang))
			{
				$error *= $this->min($value, $config);
				$error *= $this->max($value, $config);
			}
	
			return $error;
		}

		function requires($value, $config, $lang)
		{
			if (is_string($value) && $value == "")
			{
				if ($config['require'] && (is_string($value) || (is_array($value) && isset($value[System::$lazy['_locals']['default'][$lang]]) && $value[System::$lazy['_locals']['default'][$lang]] == "")))
				{
					$this->debug->add("empty_value", ERROR, array('name' => $config['name'], 'object' => $config['code'], 'lang' => $lang));
					return FALSE;
				}
			}
			
			return TRUE;
		}
		
		function min($value, $config)
		{
			if ($config['min'] > 0 && $value < $config['min'])
			{
				$this->debug->add("min_text", ERROR, array('name' => $config['min'], 'min' => $config['min'], 'object' => $config['code']));
				return FALSE;
			}
			
			return TRUE;
		}
		
		function max($value, $config)
		{
			if ($config['max'] > 0 && $value > $config['max'])
			{
				$this->debug->add("max_text", ERROR, array('name' => $config['min'], 'max' => $config['max'], 'object' => $config['code']));
				return FALSE;
			}
			
			return TRUE;
		}
		
		function checkboxes($value, $congfig, $lang)
		{
			if ($config['require'] && ! $value)
			{
				$this->debug->add("empty_value", ERROR, array('name' => $config['name'], 'object' => $config['code']));
				return FALSE;
			}
			
			return TRUE;
		}
	}