<?php

	if ( ! function_exists("load"))
	{
		function &load($class, $directory = "libraries", $is_unit = FALSE)
		{
			static $_load = array();
			global $_config;
			
			$class = ucfirst($class);
			if (isset($_load[$class]))
			{
				return $_load[$class];
			}

			if ($directory == "database")
			{
				loaded($class);
				$_load[$class] = load_database();
				return $_load[$class];
			}
			else
			{
				$path = ($is_unit) ? UNITS.$class.'/'.TYPE.'/'.$class.'.php' : CORE.$directory.'/'.$class.'.php';
				if (file_exists($path))
				{
					if ($is_unit && ! class_exists('Unit'))
					{
						load('Unit', 'system');
					}
					
					require($path);
					
					loaded($class);
					if (isset($_config) && isset($_config[$class]))
					{
						$_load[$class] = new $class($_config[$class]);
					}
					else {
						$_load[$class] = new $class();
					}
					
					return $_load[$class];
				}
				else
				{
					exit('Class '.$class.'.php not found');
				}
			}
		}
	}
	
	if ( ! function_exists("load_database"))
	{
		function load_database()
		{
			if (class_exists('CI_DB'))
			{
				return TRUE;
			}

			require_once(CORE.'database/DB.php');
			return DB();
		}
	}

	if ( ! function_exists('loaded'))
	{
		function loaded($class = "")
		{
			static $_loaded = array();
			if ($class != "")
			{
				$_loaded[strtolower($class)] = $class;
			}

			return $_loaded;
		}
	}
	
	if ( ! function_exists('lazy_error_handler'))
	{
		function lazy_error_handler($level, $message, $filepath, $line)
		{
			$_debug =& load('Debug', 'units', TRUE);
			$_debug->php($level, $message, $filepath, $line);
		}
	}
	
	if ( ! function_exists('_langs'))
	{
		function _langs($key)
		{
			return (isset(System::$lazy['_langs'][$key])) ? System::$lazy['_langs'][$key] : "[".$key."]";
		}
	}
	
	if ( ! function_exists('_debug'))
	{
		function _debug($var_name, $level = 0)
		{
			if (class_exists('System') && System::$lazy['ajax'])
			{
				echo "debug::".json_encode($var_name)."::end";
			}
			else
			{
				$type = gettype($var_name);
				$value = (is_bool($var_name) ? ($var_name ? 'TRUE' : 'FALSE') : $var_name);
		
				if (is_null($value))
				{
					$value = 'undefined';
					$type = 'undefined';
				}
		
				$temp = "";
				if (is_array($value))
				{
					$temp .= "<div style='margin-left: 40px;'>";
					foreach ($value as $key => $val)
					{
						$temp .= "[".$key."] => ";
						if (is_array($val))
						{
							$temp .= "Array "._debug($val, ($level + 1))."<br />";
						}
						elseif (is_object($val))
						{
							$temp .= "Object "._debug($val, ($level + 1))."<br />";
						}
						elseif (! is_resource($val))
						{
							$temp .= (is_bool($val) ? ($val ? 'TRUE' : 'FALSE') : str_replace(array("\n", "\t"), array('<br />', '&nbsp;&nbsp;'), htmlspecialchars($val, ENT_QUOTES)))."<br />";
						}
					}
					$temp .= "</div>";
				}
				elseif (is_object($value))
				{
					$temp .= "<div style='margin-left: 40px;'>";
					foreach (get_object_vars($value) as $key => $val)
					{
						$temp .= "[".$key."] => ";
						if (is_array($val))
						{
							$temp .= "Array "._debug($val, ($level + 1))."<br />";
						}
						elseif (is_object($val))
						{
							$temp .= "Object "._debug($val, ($level + 1))."<br />";
						}
						elseif (! is_resource($val))
						{
							$temp .= (is_bool($val) ? ($val ? 'TRUE' : 'FALSE') : str_replace(array("\n", "\t"), array('<br />', '&nbsp;&nbsp;'), htmlspecialchars($val, ENT_QUOTES)))."<br />";
						}
					}
					$temp .= "</div>";
				}
				else
				{
					$temp = str_replace(array("\n", "\t"), array('<br />', '&nbsp;&nbsp;'), $value);
				}
					
				if ($level == 0)
				{
					$result = "<div style='box-shadow: 0px 0px 3px #000; position: relative; z-index: 20000; top: 5px; left: 5px; padding: 15px; font: 11px/13px arial, sans-serif; color: #ffffff; background: #000000; opacity: 0.6; border-radius: 3px; float: left;'>";
					$result .= "<b>Type : </b>  ".$type."<br />";
					$result .= "<b style='display: block; float: left;'>Value : </b>";
					$result .= $temp;
					$result .= "</div>";
					
					echo $result;
				}
				else	
				{
					return $temp;
				}
			}
		}
	}

	if ( ! function_exists('set_status_header'))
	{
		function set_header($code = 200, $text = "")
		{
			$status = array(200	=> 'OK',
							201	=> 'Created',
							202	=> 'Accepted',
							203	=> 'Non-Authoritative Information',
							204	=> 'No Content',
							205	=> 'Reset Content',
							206	=> 'Partial Content',

							300	=> 'Multiple Choices',
							301	=> 'Moved Permanently',
							302	=> 'Found',
							304	=> 'Not Modified',
							305	=> 'Use Proxy',
							307	=> 'Temporary Redirect',

							400	=> 'Bad Request',
							401	=> 'Unauthorized',
							403	=> 'Forbidden',
							404	=> 'Not Found',
							405	=> 'Method Not Allowed',
							406	=> 'Not Acceptable',
							407	=> 'Proxy Authentication Required',
							408	=> 'Request Timeout',
							409	=> 'Conflict',
							410	=> 'Gone',
							411	=> 'Length Required',
							412	=> 'Precondition Failed',
							413	=> 'Request Entity Too Large',
							414	=> 'Request-URI Too Long',
							415	=> 'Unsupported Media Type',
							416	=> 'Requested Range Not Satisfiable',
							417	=> 'Expectation Failed',

							500	=> 'Internal Server Error',
							501	=> 'Not Implemented',
							502	=> 'Bad Gateway',
							503	=> 'Service Unavailable',
							504	=> 'Gateway Timeout',
							505	=> 'HTTP Version Not Supported');

			if ($code == '' || ! is_numeric($code))
			{
				exit('Status codes must be numeric');
			}

			if (isset($status[$code]) && $text == "")
			{
				$text = $status[$code];
			}

			if ($text == "")
			{
				exit('Status code is wrong');
			}

			$protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;
			if ($protocol == 'HTTP/1.1' || $protocol == 'HTTP/1.0')
			{
				header($protocol." {$code} {$text}", TRUE, $code);
			}
			else
			{
				header("HTTP/1.1 {$code} {$text}", TRUE, $code);
			}
		}
	}

	if ( ! function_exists('_clean'))
	{
		function _clean($string, $url_encoded = TRUE)
		{
			$bad = array();
			if ($url_encoded)
			{
				$bad[] = '/%0[0-8bcef]/';
				$bad[] = '/%1[0-9a-f]/';
			}
			$bad[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
			
			do
			{
				$string = preg_replace($bad, '', $string, -1, $count);
			}
			while ($count);
			return $string;
		}
	}