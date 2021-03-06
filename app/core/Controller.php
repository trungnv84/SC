<?php
defined('ROOT_DIR') || exit;

class Controller
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
        else App::end(404, 'default action not found.');
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
    public function view($view, $controller = null, $template = null, $layout = null, $type = null)
    {
        if (is_null($controller)) $controller = $this->_name;
        if (App::view_exists($view, $controller, $template))
            App::view($view, $controller, $template, $layout, $type, $this);
        else
            App::end(404, "$view view not found");
    }
}