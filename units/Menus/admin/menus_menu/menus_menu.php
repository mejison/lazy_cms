<?php
	$admins_folder = "panel";
	if (count($menus_menu) > 0)
	{
		echo "<div class='menus'>";
		for ($i = 0; $i < count($menus_menu); $i++)
		{
			echo "<div class='menus_item' onmouseover='this.className += \" hover\";' onmouseout='this.className = this.className.replace(\" hover\", \"\");'>";
			echo "<a href='javascript:void(0);' class='link_menus_item'>".$menus_menu[$i]['name']."<span></span></a>";
			if (isset($menus_menu[$i]['units']) && count($menus_menu[$i]['units']) > 0)
			{
				echo "<div class='sub_menus_box'>";
				$units = $menus_menu[$i]['units'];
				for ($j = 0; $j < count($units); $j++)
				{
					$url = "/".$admins_folder."/".$_locals['this']."/".$units[$j]['folder']."/";
					
					$class = ($url == $_SERVER['REQUEST_URI']) ? " active" : "";
					echo "<div class='sub_menus_item".$class."' onmouseover='this.className += \" hover\";' onmouseout='this.className = this.className.replace(\" hover\", \"\");'>";
					echo "<a href='".(($class == "") ? $url : "javascript:void(0);")."' class='link_sub_menu_item'>".$units[$j]['name']."</a>";
					echo "</div>";
					
					if ($j < count($units) - 1)
					{
						echo "<div class='sub_menus_sep'></div>";
					}
				}
				echo "</div>";
			}
			echo "</div>";
		}
		echo "</div>";
	}
