<?php

	class Access extends Unit
	{
		var $_unit = "";
		
		static $user = array();
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($info, $params = array())
		{
			if ($info['folder'] == 'access_signin')
			{
				$this->fields->get($info);
			}

			return $this->files->block($info);
		}
		
		function logged_in()
		{
			self::$user['logged_in'] = $this->sessions->get(TYPE."_logged_in") ? TRUE : FALSE;
			if (self::$user['logged_in'])
			{
				self::$user['id'] = $this->sessions->get("id");
				self::$user['login'] = $this->sessions->get("login");
				self::$user['cat'] = $this->sessions->get("cat");
				self::$user['level'] = $this->sessions->get("level");
				self::$user['create'] = $this->sessions->get("create");
				self::$user['edit'] = $this->sessions->get("edit");
				self::$user['remove'] = $this->sessions->get("remove");
				self::$user['pub'] = $this->sessions->get("pub");

				return $this->debug->add("ok_logged_in");
			}
			else
			{
				return $this->debug->add("error_logged_in", ERROR);
			}
		}
		
		function check()
		{
			if (self::$user['logged_in'])
			{
				$this->db->where("blocks_id", System::$lazy['_this']['block']);
				$this->db->where("blocks_type", TYPE);
				$this->db->where("blocks_active", TRUE);
				$this->db->where("blocks_level >=", self::$user['level']);
				$this->db->where("blocks_logged_in >=", 1);
				$this->db->limit(1);
				
				if ($this->db->count_all(System::$lazy['_this']['unit']."_blocks") > 0)
				{
					return $this->check_block();
				}
				else
				{
					return $this->debug->log("error_access", ERROR);
				}
			}
			else
			{
				return TRUE;
			}
		}
		
		function sign_in()
		{
			echo "Login";
			exit;
		}
		
		function lock()
		{
			echo "No access";
			exit;
		}
		
		function check_block($blocks_id = 0, $unit = "")
		{
			$blocks_id = ($blocks_id == 0) ? System::$lazy['_this']['block'] : $blocks_id;
			$unit = ($unit == "") ? System::$lazy['_this']['unit'] : $unit;
			 
			if ($this->access_rules())
			{
				if (count(System::$lazy['blocks_only']) > 0)
				{
					$blocks_access = FALSE;
					foreach (System::$lazy['blocks_only'] as $row_access)
					{
						if ($blocks_id == $row_access['blocks_id'] && $unit == $row_access['blocks_unit'])
						{
							$blocks_access = TRUE;
						}
					}
					
					return $this->debug->log("access_".(($blocks_access) ? "ok" : "admin"), ($blocks_access) ? OK : ERROR);
				}
				else
				{
					if (count(System::$lazy['blocks_except']) > 0)
					{
						$blocks_access = TRUE;
						foreach (System::$lazy['blocks_except'] as $row_access)
						{
							if ($blocks_id == $row_access['blocks_id'] && $unit == $row_access['blocks_unit'])
							{
								$blocks_access = FALSE;
							}
						}

						return $this->debug->log("access_".(($blocks_access) ? "ok" : "admin"), ($blocks_access) ? OK : ERROR);
					}
					else
					{
						return $this->debug->log("access_ok", OK);
					}
				}
			}
			else
			{
				return TRUE;
			}
		}

		function access_rules()
		{
			$this->db->select("blocks_id, blocks_unit, blocks_only, blocks_except");
			$this->db->where("admins_id", self::$user['id']);
			
			System::$lazy['blocks_only'] = array();
			System::$lazy['blocks_except'] = array();
			
			$_values['table'] = "admins_access";
			if ($result = $this->db->get("admins_access"))
			{
				if ($result->num_rows() > 0)
				{
					foreach ($result->result_array() as $row)
					{
						if ($row['blocks_only'])
						{
							System::$lazy['blocks_only'][] = $row;
						}
						else
						{
							if ($row['blocks_except'])
							{
								System::$lazy['blocks_except'][] = $row;
							}
						}
					}
					
					return $this->debug->add("rules_ok");
				}
				else
				{
					return $this->debug->add("empty", WARNING, $_values);
				}
			}
			else
			{
				return $this->debug->add("get", ERROR, $_values);
			}
		}
	}