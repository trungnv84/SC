<?php
defined('ROOT_DIR') || exit;

class Controller
{
	function indexAction()
	{
		if (method_exists($this, 'defaultAction'))
			$this->defaultAction();
		else App::end('none action -> 404//zzz');
	}

	function assign($key, $value = NULL)
	{
		App::assign($key, $value);
	}

}