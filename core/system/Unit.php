<?php

	class Unit
	{
		function __construct()
		{
		}
		
		function __get($key)
		{
			$Lazy =& get_instance();
			return $Lazy->$key;
		}
	}