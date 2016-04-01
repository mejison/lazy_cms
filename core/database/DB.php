<?php

	function &DB()
	{
		if ( ! file_exists($path = CORE.'database/DB_connect.php'))
		{
			exit('The configuration file doesn\'t exist');
		}
		
		include($path);

		if ( ! isset($db) OR count($db) == 0)
		{
			exit('No database connection settings were found in the database config file');
		}

		require_once(CORE.'database/DB_driver.php');
		require_once(CORE.'database/DB_active_rec.php');
		
		if ( ! class_exists('CI_DB'))
		{
			eval('class CI_DB extends CI_DB_active_record { }');
		}
		
		require_once(CORE.'database/drivers/'.$db['dbdriver'].'/'.$db['dbdriver'].'_driver.php');

		$driver = 'CI_DB_'.$db['dbdriver'].'_driver';
		$DB = new $driver($db);

		if ($DB->autoinit == TRUE)
		{
			$DB->initialize();
		}

		return $DB;
	}