<?php
defined('ROOT_DIR') || exit;

class HomeController extends Controller
{
	function defaultAction()
	{
		$user = new UserModel;
		$user->a = 5;
		var_dump(json_encode($user));
		$reflect = new ReflectionClass($user);
		var_dump($reflect->getProperties());
		//var_dump(UserModel::$driver);
		//echo 'default action <br>';
	}
}