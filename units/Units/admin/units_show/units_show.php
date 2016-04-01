<?php
	echo "<div class='show_block' id='".$this_id."'>";
	echo "<a class='add_block' onclick='lz.blocks.add(event, \"units_add\", \"units\")'>add unit</a>";
	
	if (isset($units) && ($count = count($units)) > 0)
	{
		echo "<table class='table table_hundret'>";
		echo "<tr class='tr_head'>";
		
		echo "<td class='td_id'>";
		echo "<p>".$_langs['table_id']."</p>";
		echo "</td>";
		
		echo "</tr>";
		
		for ($i = 0; $i < $count; $i++)
		{
			echo "<tr>";
		
			echo "<td class='td_id'>";
			echo "<p>".$units[$i]['id']."</p>";
			echo "</td>";
			
			echo "</tr>";
		}
		echo "</table>";
		
		echo "<script> lz.blocks.list['".$this_id."'].items_list = ".json_encode($units)."; </script>";
	}
	
	echo "</div>";