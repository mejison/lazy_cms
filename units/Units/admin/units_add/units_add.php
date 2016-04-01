<div class='add_box' id='<?php echo $this_id; ?>'>
	<table class='table_hundret'>
		<tr>
			<td class='td_texts'>
				<?php echo $this->fields->put('items_name', $this_id); ?>
			</td>
		</tr>
		<tr>
			<td class='td_texts'>
				<?php echo $this->fields->put('cats_id', $this_id, $cats_list); ?>
			</td>
		</tr>
		<tr>
			<td class='td_texts'>
				<?php echo $this->fields->put('items_folder', $this_id); ?>
			</td>
		</tr>
		<tr>
			<td class='td_texts'>
				<?php echo $this->fields->put('items_pos', $this_id); ?>
			</td>
		</tr>
		<tr>
			<td class='td_texts'>
				<?php echo $this->fields->put('items_active', $this_id); ?>
			</td>
		</tr>
	</table>
	<?php echo $this->fields->submit(); ?>
</div>