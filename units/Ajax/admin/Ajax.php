<?php

	class Ajax extends Unit
	{
		var $_unit = "";
		function __construct()
		{
			parent::__construct();
			$this->_unit = strtolower(get_class($this));
		}
		
		function output($post)
		{
			System::$lazy['_print'] = TRUE;
			$responce = array();
			$responce['data'] = $this->ajax_content(json_decode(stripslashes($post['post_mas']), TRUE));
			$responce['time'] = $this->debug->end('ajax', TRUE);

			echo "ajax::".json_encode($responce);
		}

		function ajax_content($post)
		{
			$content = array();
			$count = count($post);
			if ($count > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					Debug::$_errors = array();
					
					$items = $post[$i];
					
					$content_array = array();
					$content_array['content'] = "";
					$content_array['result'] = FALSE;
					$content_array['errors'] = array();
					
					$this->load->unit($items['unit']);
					
					if (method_exists($this->$items['unit'], $items['method']))
					{
						if ($content_array['content'] = $this->$items['unit']->$items['method']($items['data']))
						{
							$content_array['result'] = TRUE;
						}
					}
					else
					{
						$this->debug->add("method_empty");
					}
					
					$this->debug->load();
					$this->debug->set();
					
					$content_array['errors'] = Debug::$_errors;
					$content[$items['id']] = $content_array;
				}
			}
			
			return $content;
		}
	}