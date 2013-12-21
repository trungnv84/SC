<?php
defined('ROOT_DIR') || exit;

abstract class Controller
{
    protected $_name;

    public function __construct()
    {
        $this->_name = preg_replace('/Controller$/', '', get_class($this));
    }

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

    /**
     * Controller::view //ccc
     *
     * @param string $view
     * @param string $controller
     * @param string $template
     * @param string $layout
     * @param string $type
     */
    protected function view($view, $controller = null, $template = null, $layout = null, $type = null)
    {
        if (is_null($controller)) $controller = $this->_name;
        if (App::view_exists($view, $controller, $template))
            App::view($view, $controller, $template, $layout, $type);
        else
            App::end('none view -> 404//zzz');
    }
}