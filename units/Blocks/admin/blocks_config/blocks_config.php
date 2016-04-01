<?php 

	echo "<script type='text/javascript' data-remove='remove'> lz.fields.list = ".json_encode((isset($_config['fields'])) ? $_config['fields'] : array())."; </script>";