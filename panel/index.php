<?php

	header('Content-Type: text/html; charset=utf-8');
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	$time_temp = microtime(TRUE);

	define('ROOT', $_SERVER['DOCUMENT_ROOT']."/");
	define('CORE', ROOT."core/");
	define('UNITS', ROOT."units/");
	define('TYPE', "admin");
	define('LANG', "ua");
	define('APP', ROOT.TYPE."/");

	define('ERROR', '0');
	define('WARNING', '1');
	define('OK', '2');
	define('TEXT', '3');
	define('WAIT', '4');

	define('LOG', '0');
	define('USER', '1');
	define('HINT', '2');
	
	require_once CORE.'system/Core.php';