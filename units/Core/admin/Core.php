<?php

	class Core extends Unit
	{
		static $puts = array();
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($info, $params = array())
		{
			return $this->files->block($info);
		}
		
		function browser()
		{
			$agent = $_SERVER['HTTP_USER_AGENT'];
			preg_match("/(MSIE|Opera|Firefox|Chrome|Version|Opera Mini|Netscape|Konqueror|SeaMonkey|Camino|Minefield|Iceweasel|K-Meleon|Maxthon)(?:\/| )([0-9.]+)/", $agent, $browser_info);

			list(, $browser, $version) = $browser_info;
			if (preg_match("/Opera ([0-9.]+)/i", $agent, $opera))
			{
				$version = $opera[1];
			}

			if ($browser == 'MSIE')
			{
				preg_match("/(Maxthon|Avant Browser|MyIE2)/i", $agent, $ie);
				if ($ie)
				{
					$browser = $ie[1];
				}
				else
				{
					$browser = "IE";
				}
			}

			if ($browser == 'Firefox')
			{
				preg_match("/(Flock|Navigator|Epiphany)\/([0-9.]+)/", $agent, $ff);
				if ($ff)
				{
					$browser = $ff[1];
					$version = $ff[2];
				}
			}

			if ($browser == 'Opera' && $version == '9.80')
			{
				$version = substr($agent, -5);
			}

			if ($browser == 'Version')
			{
				$browser = 'Safari';
			}

			if ( ! $browser && strpos($agent, 'Gecko'))
			{
				$browser = 'Gecko';
			}
			
			$part = explode(".", $version);
			$version = $part[0];
			$browser = strtolower($browser);
			
			switch ($browser)
			{
				case "firefox": return ($version < 10) ? FALSE : TRUE; break;
				case "opera": return ($version < 10) ? FALSE : TRUE; break;
				case "chrome": return ($version < 18) ? FALSE : TRUE; break;
				case "ie": return ($version < 9) ? FALSE : TRUE; break;
				case "safari": return ($version < 5) ? FALSE : TRUE; break;
				default: return FALSE; break;
			}
		}
	}