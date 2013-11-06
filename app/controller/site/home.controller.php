<?php
defined('ROOT_DIR') || exit;

class HomeController extends Controller
{
	function defaultAction()
	{
		echo '<div>Start Action: ', microtime() - MICRO_TIME_NOW, '</div>';

		echo '<pre>';
		/*$user = new UserModel;
		$user->id = 99;
		$user->a = 5;
		var_dump(json_encode($user));
		$reflect = new ReflectionClass($user);
		var_dump($reflect->getProperties(ReflectionProperty::IS_STATIC));
		var_dump(get_object_vars($user));
		var_dump(get_class_vars('UserModel'));
		$data = $user->getData('both');
		var_dump($data->id, $data['id'], json_encode($data));*/

		$rs = UserModel::query('SELECT * FROM sp_users');

		var_dump($rs);

		echo '</pre>';

		echo '<div>End Action: ', microtime() - MICRO_TIME_NOW, '</div>';
	}
}