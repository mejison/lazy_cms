<?php

	class Uri
	{
		var $uri_string = FALSE;
		var $segments = array();
		
		function segment($i, $empty = FALSE)
		{
			if ( ! $this->uri_string)
			{
				$this->_get_uri_string();
				$this->_explode_segments();
				$this->_reindex_segments();
			}

			return ( ! isset($this->segments[$i])) ? $empty : $this->segments[$i];
		}

		function _get_uri_string()
		{
			$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
			if (trim($path, '/') != '' && $path != "/".SELF)
			{
				$this->_set_uri_string($path);
				return;
			}

			$path = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
			if (trim($path, '/') != '')
			{
				$this->_set_uri_string($path);
				return;
			}

			if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != "")
			{
				$this->_set_uri_string(key($_GET));
				return;
			}

			$this->uri_string = "";
			return;
		}

		function _set_uri_string($string)
		{
			$string = _clean($string, FALSE);
			$this->uri_string = ($string == '/') ? '' : $string;
		}
		
		function _explode_segments()
		{
			foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->uri_string)) as $val)
			{
				$val = trim($this->_filter_uri($val));

				if ($val != "")
				{
					$this->segments[] = $val;
				}
			}
		}
		
		function _filter_uri($string)
		{
			if ($string != "")
			{
				$chars = 'a-z 0-9~%.:_\-';
				if ( ! preg_match("|^[".str_replace(array('\\-', '\-'), '-', preg_quote($chars, '-'))."]+$|i", $string))
				{
					exit('The URI has forbidden characters');
				}
			}

			$bad = array('$', '(', ')', '%28', '%29');
			$good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');
			
			return str_replace($bad, $good, $string);
		}
		
		function _reindex_segments()
		{
			array_unshift($this->segments, NULL);
			unset($this->segments[0]);
		}

		function redirect($uri = "/")
		{
			header("Location: ".$uri, TRUE, 302);
		}
	}