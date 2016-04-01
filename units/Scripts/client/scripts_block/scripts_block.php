<?php

	if (isset($js) && ($count = count($js)) > 0)
	{
		for ($i = 0; $i < $count; $i++)
		{
			echo "<script type='text/javascript' src='".$js[$i]."'></script>\n";
		}
	}