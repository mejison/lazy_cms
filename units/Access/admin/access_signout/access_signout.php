<div class='signout_box' id='<?php echo $this_id; ?>'>
	<a href='javascript:void(0);' class='link_signout'><?php echo Access::$user['login']; ?><span>[<?php echo mb_strtolower(Access::$user['cat'], "utf-8"); ?>]</span></a>
	<span class='logout_arrow'>&nbsp;&nbsp;</span>
	<a href='javascript:void(0);' id='link_signout' class='link_logout'><?php echo $_langs['admins_logout']; ?></a>
</div>