<?php
defined('ROOT_DIR') || exit;

abstract class Controller
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
		if (App::view_exists($view, $controller, $template)) {
			App::view($view, $controller, $template, $layout, $type);
		} App::end('none view -> 404//zzz');
	}
}