<?php
defined('ROOT_DIR') || exit;

class view
{
	function generate($data, $template = DEFAULT_TEMPLATE)
	{
		if(is_scalar($data))
			echo $data;
		else
			echo json_encode($data);
	}
}