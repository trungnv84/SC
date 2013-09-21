<?php
defined('ROOT_DIR') || exit;

class HomeController extends Controller
{
	function defaultAction()
	{
		echo '<pre>';
		$user = new UserModel;
		$user->a = 5;
		var_dump(json_encode($user));
		$reflect = new ReflectionClass($user);
		var_dump($reflect->getProperties(ReflectionProperty::IS_STATIC));
		var_dump(get_object_vars($user));
		var_dump(get_class_vars('UserModel'));
		//echo 'default action <br>';
		echo '</pre>';
	}
}