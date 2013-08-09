<?php
defined('ROOT_DIR') || exit;

class Controller
{

	/*function __construct()
	{
	}*/

	function indexAction()
	{
		if (method_exists($this, 'defaultAction'))
			$this->defaultAction();
		else app()->end('none action -> 404//zzz');
	}

	function view($name, $controller, $layout = DEFAULT_LAYOUT, $template = DEFAULT_TEMPLATE, $view_type = DEFAULT_VIEW_TYPE)
	{
		if (isset($this->vars) && is_array($this->vars))
			foreach ($this->vars as $key => &$value) $$key =& $value;
		$file = ROOT_DIR . DS . 'app' . DS . 'template' . DS . $template . DS . strtolower($controller) . DS . strtolower($name) . '.php';
		if (file_exists($file)) {
			ob_start();
			require $file;
			$_main = ob_get_contents();
			ob_end_clean();
			$view =& app()->getView($view_type);
			$view->generate($_main, $layout, $template);
		} else app()->end('none view -> 404//zzz');
	}

	function assign(&$key, &$value = NULL)
	{
		if (is_array($key)) {
			foreach ($key as $k => &$v)
				$this->vars[$k] = & $v;
		} else $this->vars[$key] =& $value;
	}
}