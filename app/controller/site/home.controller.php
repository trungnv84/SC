
<?php
defined('ROOT_DIR') || exit;

class HomeController extends Controller
{
	function defaultAction()
	{
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

		/*$rs = UserModel::query('SELECT * FROM users');
		var_dump(mysql_fetch_row ( $rs ));*/

		//8/0;a

		/*$rs = UserModel::query('SELECT * FROM users');
		var_dump($rs);*/

		/*
		$filterInput = Joomla\JFilterInput::getInstance();
		echo $filterInput->clean('<script>alert("abc");</script>');
		echo '<br />';
		*/

		/*$_GET['a'] = '<script>alert("abc");</script>';
		print_r(App::getVarHTML('a'));*/

		$time = explode(' ', MICRO_TIME_NOW);
		$time = date('Y-m-d h:m:s', TIME_NOW) . ' ' . substr($time[0], 2, 6) . rand();
		var_dump($time);
		var_dump(date('Y-m-d h:m:s', strtotime($time)));

		echo '</pre>';
	}
}