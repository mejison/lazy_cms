<?php
	$lazy = System::$lazy;
	unset($lazy['blocks']);

	echo "<script type='text/javascript' src='/units/Core/admin/_files/!lazy.js'></script>";
	echo "<script type='text/javascript' data-remove='remove'> lz.lazy(".json_encode($lazy)."); </script>";