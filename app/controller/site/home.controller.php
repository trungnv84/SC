
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

		$user =& App::getModel('User');
		$user->query('SELECT * FROM users');
		$user->setFetchMode(DBDriver::FETCH_ACT_OBJ, 'UserModel');
		var_dump($user->fetch());

		/*
		$filterInput = Joomla\JFilterInput::getInstance();
		echo $filterInput->clean('<script>alert("abc");</script>');
		echo '<br />';
		*/

		/*$_GET['a'] = '<script>alert("abc");</script>';
		print_r(App::getVarHTML('a'));*/

		/*header('Content-Type:image/jpeg');
		print_r(apache_response_headers());
		print_r(headers_list());*/
		/*header('Content-Type:text/html');
		print_r(headers_list());*/

		echo '</pre>';
	}
}