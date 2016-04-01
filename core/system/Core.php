<?php

	require_once(CORE.'system/Functions.php');
	require_once(CORE.'system/Config.php');
	
	$_debug =& load('Debug', 'units', TRUE);
	set_error_handler('lazy_error_handler');

	$_db =& load('DB', 'system');
	$_uri =& load('URI', 'system');

	require_once(CORE.'system/Lazy.php');
	function &get_instance()
	{
		return Lazy::get_instance();
	}


	$class = "System";
	$method = "index";
	require_once(APP.$class.'.php');

	$system = new $class();
	$system->$method();

	if (class_exists('CI_DB') AND isset($system->db))
	{
		$system->db->close();
	}