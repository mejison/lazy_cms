<?php
	if (isset($js) && count($js) > 0)
	{
		for ($i = 0; $i < count($js); $i++)
		{
			echo "<script type='text/javascript' src='".$js[$i]."'></script>\n";
		}
	}