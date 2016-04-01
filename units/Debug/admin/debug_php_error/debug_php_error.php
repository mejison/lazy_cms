<div style='border: solid #e80000 1px; margin: 10px; padding: 20px;'>
	<h3 style='font: bold 15px/1.3 arial, sans-serif; color: #000; margin: 0; padding-bottom: 10px;'>
		<?php echo $_langs['php_error_title']; ?>
	</h3>
	
	<p style='font: 13px/1.3 arial, sans-serif; color: #000;'>
		<b><?php echo $_php_error['level']; ?></b> - <?php echo $_langs[$_php_error['level']]; ?>
	</p>
	
	<p style='font: 13px/1.3 arial, sans-serif; color: #000;'>
		<?php echo $_langs['php_message']; ?>: <?php echo $_php_error['message']; ?>
	</p>
	
	<p style='font: 13px/1.3 arial, sans-serif; color: #000;'>
		<?php echo $_langs['php_file']; ?>: <?php echo $_php_error['file']; ?>
	</p>
	
	<p style='font: 13px/1.3 arial, sans-serif; color: #000;'>
		<?php echo $_langs['php_line']; ?>: <?php echo $_php_error['line']; ?>
	</p>
</div>