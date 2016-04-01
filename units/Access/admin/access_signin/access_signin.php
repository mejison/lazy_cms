<div class='signin_box' id='<?php echo $this_id; ?>'>
	<div class='logo_login'>
		<p class='text_logo_login'>
			<?php echo _langs('logo_text'); ?>
			<span>v.&nbsp;0.2</span>
		</p>
	</div>

	<div class='login_panel'>
		<table class='table_hundret'>
			<tr>
				<td class='td_texts'>
					<?php echo $this->fields->put('items_login', $this_id); ?>
				</td>
			</tr>

			<tr>
				<td class='td_texts'>
					<?php echo $this->fields->put('items_pass', $this_id); ?>
				</td>
			</tr>

			<tr>
				<td class='td_login_cell'>
					<?php echo $this->fields->submit(_langs("s_enter")); ?>

					<a href='http://div-art.com/' target='_blank' class='link link_recovery' title=''><?php echo _langs('access_recovery'); ?></a>
				</td>
			</tr>
		</table>
	</div>

	<p class='text_copy_login'>
		<?php echo _langs('copy_text'); ?> &laquo;<a href='http://div-art.com/' target='_blank' title='' class='link_copy'>Div-Art</a>&raquo; &copy; <?php echo date("Y"); ?>
	</p>
</div>