<?php

	class Sessions
	{
		var $_now;
		var $_last;
		var $_to_update = 300;
		var $_lifetime = 7200;
		var $_session_name = 'lazy_session';
		var $_seance_name = 'lazy_seance';
		var $_sessions_id;
		var $_exp = 0;
		var $_seance = FALSE;
		var $_salt = '';
		var $_match_ip = FALSE;
		var $_match_agent = TRUE;
		var $_session = array();

		public function __construct()
		{
			$this->_now = time();
			$this->_salt = $_SERVER['HTTP_HOST'];

			if ( ! $this->read())
			{
				$this->create();
			}
			else
			{
				$this->update();
			}

			$this->_clear();
		}

		function read()
		{
			$cookie_session = isset($_COOKIE[$this->_session_name]) ? $_COOKIE[$this->_session_name] : FALSE;
			$cookie_seance = isset($_COOKIE[$this->_seance_name]) ? $_COOKIE[$this->_seance_name] : FALSE;

			if ($cookie_session === FALSE && $cookie_seance === FALSE)
			{
				return FALSE;
			}
			else
			{
				if ($cookie_session !== FALSE)
				{
					if (strlen($cookie_session) == 59)
					{
						$temp = array_diff(explode(":", $cookie_session), array(''));
						if (count($temp) != 3)
						{
							return FALSE;
						}
						else
						{
							$this->_sessions_id = $temp[0];
							$this->_exp = (int) $temp[1];
							$this->_last = (int) $temp[2];
						}
					}
					else
					{
						return FALSE;
					}
				}
				
				if ($cookie_seance !== FALSE)
				{
					if (strlen($cookie_seance) == 50)
					{
						$temp = array_diff(explode(":", $cookie_seance), array(''));
						if (count($temp) != 3)
						{
							return FALSE;
						}
						else
						{
							$this->_sessions_id = $temp[0];
							$this->_last = (int) $temp[2];
							$this->_seance = TRUE;
						}
					}
					else
					{
						return FALSE;
					}
				}
			}

			$hash = $this->hash();
			if (substr($this->_sessions_id, 0, 32) !== $hash)
			{
				$this->destroy();
				return FALSE;
			}

			if ($this->_exp < $this->_now && $this->_exp !== 0)
			{
				$this->destroy();
				return FALSE;
			}

			$this->_get();

			if ( ! is_array($this->_session))
			{
				$this->destroy();
				return FALSE;
			}

			return TRUE;
		}

		function write()
		{
			$max_exp = 0;
			$session = array();
			$seance = array();
			
			foreach ($this->_session as $key => $row)
			{
				$row['exp'] = ($row['time'] === FALSE) ? ($this->_now + $this->_lifetime) : (($row['time'] == 0) ? 0 : ($this->_now + $row['time']));
				$this->_session[$key]['exp'] = $row['exp'];
				if ($row['exp'] > 0)
				{
					$session[$key] = $row;
				}
				else
				{
					$seance[$key] = $row;
				}
				$max_exp = max($max_exp, $row['exp']);
			}

			if (count($session))
			{
				$this->write_file(json_encode($session));
			}
			else
			{
				$this->destroy_session();
			}
			
			if (count($seance))
			{
				$this->write_file(json_encode($seance), TRUE);
			}
			else
			{
				$this->destroy_seance();
			}
			$this->_set($max_exp);
		}

		function create()
		{
			mt_srand();
			$this->_sessions_id = $this->hash().mt_rand(10000, 99999);
			if ($this->write_file() !== FALSE)
			{
				$this->_set();
			}
		}
	
		function write_file($data = "", $seance = FALSE)
		{
			$folder = ($seance) ? 0 : $this->folder();
			if ( ! file_exists($path = ROOT."sessions/".$folder."/"))
			{
				if ( ! mkdir($path, 0777, TRUE))
				{
					return FALSE;
				}
			}

			return file_put_contents($path.$this->_sessions_id.".ses", $data);
		}

		function update()
		{
			if (($this->_last + $this->_to_update) >= $this->_now)
			{
				return;
			}

			$this->_set();
		}

		function destroy()
		{
			$this->_session = array();
			$this->destroy_session();
			$this->destroy_seance();
		}
		
		function destroy_session()
		{
			setcookie($this->_session_name, "", ($this->_now - 31500000), "/", "", FALSE);
		}
		
		function destroy_seance()
		{
			setcookie($this->_seance_name, "", ($this->_now - 31500000), "/", "", FALSE);
		}

		function _set($exp = FALSE)
		{
			$exp = ($exp === FALSE) ? ($this->_now + $this->_lifetime) : $exp;
			$name = ($exp === 0) ? $this->_seance_name : $this->_session_name;
			setcookie($this->_session_name, $this->_sessions_id.":".$exp.":".$this->_now, $exp, "/", "", FALSE);
			
			if ($exp !== 0)
			{
				$check = FALSE;
				foreach ($this->_session as $row)
				{
					if ($row['exp'] == 0)
					{
						$check = TRUE;
					}
				}
				
				if ($check)
				{
					$exp = 0;
					setcookie($this->_seance_name, $this->_sessions_id.":".$exp.":".$this->_now, $exp, "/", "", FALSE);
				}
			}
		}
		
		function _get()
		{
			$folder = $this->folder($this->_exp);
			if (file_exists($path = ROOT."sessions/".$folder."/".$this->_sessions_id.".ses"))
			{
				$this->_fetch_array(json_decode(file_get_contents($path), TRUE));
			}
			
			if ($this->_exp > 0 && $this->_seance)
			{
				if (file_exists($path = ROOT."sessions/0/".$this->_sessions_id.".ses"))
				{
					$this->_fetch_array(json_decode(file_get_contents($path), TRUE));
				}
			}
		}
		
		function _fetch_array($items)
		{
			if (is_array($items))
			{
				foreach ($items as $key => $row)
				{
					if ($row['exp'] >= $this->_now || $row['exp'] == 0)
					{
						$this->_session[$key] = $row;
					}
				}
			}
			return $this->_session;
		}
	
		function ip()
		{
			return (isset($_SERVER['HTTP_X_REAL_IP'])) ? $_SERVER['HTTP_X_REAL_IP']: $_SERVER['REMOTE_ADDR'];
		}
		
		function agent()
		{
			return (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : FALSE;
		}
		
		function hash()
		{
			$string = $this->_salt;
			if ($this->_match_ip)
			{
				$string .= $this->ip();
			}
			
			if ($this->_match_agent)
			{
				$string .= $this->agent();
			}
			return md5($string);
		}
		
		function folder($exp = FALSE)
		{
			$exp = ($exp === FALSE) ? ($this->_now + $this->_lifetime) : $exp;
			return ($exp == 0) ? 0 : mktime(0, 0, 0, date("n", $exp), date("j", $exp), date("Y", $exp));
		}

		function get($key)
		{
			return ( ! isset($this->_session[$key]['value'])) ? FALSE : $this->_session[$key]['value'];
		}
		
		function all()
		{
			$tmp_session = array();
			if (isset($this->_session))
			{
				foreach ($this->_session as $key => $val)
				{
					$tmp_session[$key] = $val['value'];
				}
				
				return count($tmp_session) ? $tmp_session : FALSE;
			}
			
			return FALSE;
		}

		function set($data = array(), $val = FALSE, $lifetime = FALSE)
		{
			$time = $val;
			if (is_string($data))
			{
				$data = array($data => $val);
				$time = $lifetime;
			}

			if (count($data) > 0)
			{
				foreach ($data as $key => $val)
				{
					$this->_session[$key]['value'] = $val;
					$this->_session[$key]['exp'] = 0;
					$this->_session[$key]['time'] = $time;
				}
			}

			$this->write();
		}

		function remove($data = array())
		{
			if (is_string($data))
			{
				$data = array($data);
			}

			if (count($data) > 0)
			{
				foreach ($data as $val)
				{
					unset($this->_session[$val]);
				}
			}

			$this->write();
		}

		function _clear()
		{
			$folders = array_diff(scandir(ROOT."sessions/"), array('.', '..'));
			foreach ($folders as $folder)
			{
				if ($folder < $this->_now - (60 * 60 * 24) && $folder != 0)
				{
					$temp = array_diff(scandir(ROOT."sessions/".$folder."/"), array('.', '..'));
					foreach($temp as $file)
					{
						unlink(ROOT."sessions/".$folder."/".$file);
					}
					rmdir(ROOT."sessions/".$folder."/");
				}
				else
				{
					if ($folder == 0)
					{
						$temp = array_diff(scandir(ROOT."sessions/".$folder."/"), array('.', '..'));
						foreach($temp as $file)
						{
							if (filemtime(ROOT."sessions/".$folder."/".$file) < $this->_now - (60 * 60 * 24 * 7))
							{
								unlink(ROOT."sessions/".$folder."/".$file);
							}
						}
					}
				}
			}
		}
	}