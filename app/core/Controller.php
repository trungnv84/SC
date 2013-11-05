<?php
defined('ROOT_DIR') || exit;

class Controller
{
	public function indexAction()
	{
		if (method_exists($this, 'defaultAction'))
			$this->defaultAction();
		else App::end('none action -> 404//zzz');
	}

	protected function assign($key, $value = NULL)
	{
		App::assign($key, $value);
	}

	protected function view($view, $controller = CURRENT_CONTROLLER, $template = null, $layout = null, $type = null)
	{
		App::view($view, $controller, $template, $layout, $type);
	}
}