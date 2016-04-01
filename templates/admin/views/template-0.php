<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8' />
		<meta name='keywords' content='' />
		<meta name='description' content='' />
		
		<title>Lazy CMS 0.2 - Admin Panel</title>
		
		<link rel='icon' href='/panel/favicon.ico' type='image/x-icon' />
		<link rel='shortcut icon' href='/panel/favicon.ico' type='image/x-icon' />
		
		<?php echo $this->blocks->put("core_config"); ?>
		<?php echo $this->blocks->put("css_block"); ?>
		<?php echo $this->blocks->put("scripts_block"); ?>
	</head>
	
	<body>
		<div class='site min_height'>
			<?php echo $this->blocks->put("menus_content"); ?>
		</div>
		
		<div class='foot'>
			<div class='site'>
				<div class='foot_inner'>
					<?php echo $this->blocks->put("debug_block"); ?>
					<?php echo $this->blocks->put("debug_time"); ?>
				</div>
			</div>
		</div>

		<?php echo $this->blocks->put("blocks_config"); ?>
	</body>
</html>