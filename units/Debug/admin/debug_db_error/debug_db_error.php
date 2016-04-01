<div style='border: solid #e80000 1px; margin: 10px; padding: 20px;'>
	<h3 style='font: bold 15px/1.3 arial, sans-serif; color: #000; margin: 0; padding-bottom: 10px;'>
		<?php echo $_langs['db_error_title']; ?>
	</h3>

	<p style='font: 13px/1.3 arial, sans-serif; color: #000;'>
		<?php echo $_langs['db_message']; ?>: <?php echo $_db_error['message']; ?>
	</p>
	
	<p style='font: 13px/1.3 arial, sans-serif; color: #000;'>
		<?php echo $_langs['db_query']; ?>:<br /><?php echo $_db_error['query']; ?>
	</p>
	
	<p style='font: 13px/1.3 arial, sans-serif; color: #000;'>
		<?php echo $_langs['db_file']; ?>: <?php echo $_db_error['file']; ?>
	</p>
	
	<p style='font: 13px/1.3 arial, sans-serif; color: #000;'>
		<?php echo $_langs['db_line']; ?>: <?php echo $_db_error['line']; ?>
	</p>
</div>