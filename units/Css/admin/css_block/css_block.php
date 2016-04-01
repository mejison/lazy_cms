<?php
	if (isset($css) && count($css) > 0)
	{
		for ($i = 0; $i < count($css); $i++)
		{
			echo "<link rel='stylesheet' type='text/css' href='".$css[$i]."' media='screen' />\n";
		}
	}