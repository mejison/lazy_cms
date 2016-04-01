<?php
	if (isset($css) && ($count = count($css)) > 0)
	{
		for ($i = 0; $i < $count; $i++)
		{
			echo "<link rel='stylesheet' type='text/css' href='".$css[$i]."' media='screen' />\n";
		}
	}
	
	if (isset($print_css) && ($count = count($print_css)) > 0)
	{
		for ($i = 0; $i < $count; $i++)
		{
			echo "<link rel='stylesheet' type='text/css' href='".$print_css[$i]."' media='print' />\n";
		}
	}
