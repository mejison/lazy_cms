[Контент сторінки pages_show]
<? /* ?>
<div class='relative' id='<?php echo $this_block; ?>'>
	<div class='controls_units_box'>
		<a href='javascript:void(0);' onclick='_hash.add("pages_add", "pages");'><?php echo $langs['items_add']; ?></a>
	</div>
	
	<div class='actions_panel'>
		<p class='text_group'>
			<?php echo $langs['count_group']; ?>: <b id='count_group'>0</b>
		</p>
		
		<?php
			$list = array("delete" => $langs['actions_delete'],
						  "active-0" => $langs['actions_active-0'],
						  "active-1" => $langs['actions_active-1'],
						  "mark-0" => $langs['actions_mark-0'],
						  "mark-1" => $langs['actions_mark-1']);
			echo _select_print("actions_list", $list, array("onchange" => "_filter.action(this)"), "actions_list");
		?>
		
		<?php echo $this->blocks->put("locals", "locals_for_show"); ?>
		
		<?php
			if (isset($result['rows']) && $result['rows'] > 0)
			{
				echo "<p class='text_rows'>";
				echo $langs['count_rows'].": <b id='count_rows'>".$result['rows']."</b> / <b id='count_all'>".$result['rows']."</b>";
				echo "</p>";
			}
		?>
	</div>
	
	<?php
		$sort_field = isset($_config['pages_show']['cfg']['filter']['sort_field']) ? $_config['pages_show']['cfg']['filter']['sort_field'] : FALSE;
		$sort_dir = isset($_config['pages_show']['cfg']['filter']['sort_dir']) ? $_config['pages_show']['cfg']['filter']['sort_dir'] : FALSE;
	?>
	<table class='table_hundret'>
		<tr class='tr_head'>
			<td class='td_group'>
				<input type='checkbox' class='group' id='check_all' onchange='_filter.group_all(this);' />
			</td>
				<td class='td_id'>
					<a href='javascript:void(0);' onclick='_filter.sort(this, "pages_id");' class='link_sort<?php echo ($sort_field == "pages_id") ? " ".$sort_dir : ""; ?>'><?php echo $langs['sort_id']; ?></a>
				</td>
					<td class='td_name'>
						<a href='javascript:void(0);' onclick='_filter.sort(this, "pages_texts.items_name");' class='link_sort<?php echo ($sort_field == "pages_texts.items_name") ? " ".$sort_dir : ""; ?>'><?php echo $langs['sort_name']; ?></a>
					</td>
						<td class='td_check'>
							<a href='javascript:void(0);' onclick='_filter.sort(this, "pages_active");' class='link_sort<?php echo ($sort_field == "pages_active") ? " ".$sort_dir : ""; ?>'><?php echo $langs['sort_active']; ?></a>
						</td>
							<td class='td_check'>
								<a href='javascript:void(0);' onclick='_filter.sort(this, "pages_mark");' class='link_sort<?php echo ($sort_field == "pages_mark") ? " ".$sort_dir : ""; ?>'><?php echo $langs['sort_mark']; ?></a>
							</td>
								<td class='td_button'>
								</td>
									<td class='td_button'>
									</td>
		</tr>
		
		<tr class='tr_filter'>
			<td class='td_group'>
			</td>
				<td class='td_id'>
					<a href='javascript:void(0);' onclick='_filter.show(this, "text", "pages_id");' class='link_filter add'></a>
				</td>
					<td class='td_name'>
						<a href='javascript:void(0);' onclick='_filter.show(this, "text", "pages_texts.items_name");' class='link_filter add'></a>
					</td>
						<td class='td_check'>
							<a href='javascript:void(0);' onclick='_filter.show(this, "radio", "pages_active");' class='link_filter add'></a>
						</td>
							<td class='td_check'>
								<a href='javascript:void(0);' onclick='_filter.show(this, "radio", "pages_mark");' class='link_filter add'></a>
							</td>
								<td colspan='2' class='td_filter_button'>
									<a href='javascript:void(0);' onclick='_filter.clear_all();' class='link_filter remove'></a>
								</td>
		</tr>
	</table>

	<div id='result_box'>
		<?php
			if (isset($result['data']) && ($count = count($result['data'])) > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$items = $result['data'][$i];
					
					echo "<div class='row_box row_".($i % 2)."' onmouseover='_show.over(this);' onmouseout='_show.out(this);' ondblclick='_lazy_hash_add(\"pages_add\", \"".$this_unit."\", \"".Locals::$langs['default']['client']."\", \"id:".$items['id']."\");'>";
					echo "<table class='table_hundret'>";
					echo "<tr class='tr_result'>";
					
					echo "<td class='td_group'>";
					echo "<input type='checkbox' class='group' onchange='_filter.group(this, \"".$items['id']."\")' />";
					echo "</td>";
					
					echo "<td class='td_id'>";
					echo "<p class='text'>";
					echo $items['id'];
					echo "</p>";
					echo "</td>";
					
					echo "<td class='td_name'>";
					echo "<p class='text'>";
					echo $items['name'];
					echo "</p>";
					echo "</td>";
					
					echo "<td class='td_check'>";
					echo "<input type='checkbox' class='check' ".(($items['active'] == '1') ? " checked='checked'" : "")." onchange='_show.check(this, \"".$this_unit."\", \"pages_items\", \"active\", \"".$items['id']."\");' />";
					echo "</td>";
					
					echo "<td class='td_check'>";
					echo "<input type='checkbox' class='check' ".(($items['mark'] == '1') ? " checked='checked'" : "")." onchange='_show.check(this, \"".$this_unit."\", \"pages_items\", \"mark\", \"".$items['id']."\");' />";
					echo "</td>";
					
					echo "<td class='td_button'>";
					echo "<a class='button button_show edit_item' data-tip=\"".$langs['tooltip_edit_icons']."\" onclick='_lazy_hash_add(\"pages_add\", \"".$this_unit."\", \"".Locals::$langs['default']['client']."\", \"id:".$items['id']."\");'><span class='button_top'></span><span class='button_content'></span></a>";
					echo "</td>";
					
					echo "<td class='td_button'>";
					echo "<a class='button button_show delete_item' data-tip=\"".$langs['tooltip_delete_icons']."\" onclick='_lazy_block_delete(\"".$items['id']."\", \"".$this_unit."\", \"pages_add\");'><span class='button_top'></span><span class='button_content'></span></a>";
					echo "</td>";
					
					echo "</tr>";
					echo "</table>";
					echo "</div>";
					
					echo "<script>";
					echo "_filter.list[".$items['id']."] = ".json_encode($items).";";
					echo "_filter.list[".$items['id']."].style = 'row_".($i % 2)."';";
					echo "_filter.list[".$items['id']."].field_page = 1;";
					echo "</script>";
				}
			}
			else
			{
				echo "<p class='text empty_show_items'>";
				echo $langs['empty_show_items'];
				echo "</p>";
			}
		?>
	</div>
	
	<?php
		if (isset($result['pages']) && $result['pages'] > 1)
		{
			echo "<div class='pages_box'>";
			for ($i = 1; $i <= $result['pages']; $i++)
			{
				echo "<a href='javascript:void(0);' class='button button_show link_page".(($i == 1) ? " active" : "")."' onclick='_filter.page(this, \"".$i."\")'><span class='button_top'></span><span class='button_content'>".$i."</span></a>";
			}
			echo "</div>";
		}
	?>
</div>

<script>
	_filter.set_config(<?php echo json_encode($_config['pages_show']['cfg']['filter']); ?>);
	_filter.unit = "<?php echo $this_unit; ?>";
</script>
<? */ ?>