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

		//8/0;a

		/*$user =& App::getModel('User');
		$user->query('SELECT * FROM users');
		$user->setFetchMode(DBDriver::FETCH_ACT_OBJ);
		$user = $user->fetch();
		var_dump($user);*/

		/*$user = new UserModel;
		var_dump($user);*/

		/*$user = UserModel::load(array(
			'condition' => 'id = ?0 AND username LIKE :username:',
			'bind' => array(2, 'username' => 'ad%')
		));
		$user->load(10);
		var_dump($user);*/

		//UserModel::query('SELECT * FROM #__users');
		//UserModel::setFetchMode(DBDriver::FETCH_ACT_OBJ);
		/*$user = UserModel::fetchAll(array('select' => '*', 'from' => 'users'), null, true);
		var_dump($user);*/

		//UserModel::setFetchMode(DBDriver::FETCH_ACT_OBJ);
		/*$db =& App::db('Users');
		$db->setFetchMode(DBDriver::FETCH_ACT_OBJ);
		$query = $db->getQuery();
		$query->select(array('id', 'username'))
			->from($db->getConfig('dbprefix') . 'users')
			->where(array('username = ' . $db->quote('admin')))
			->setLimit(1, 1);
		$user = $db->fetchAll($query, null, true);
		var_dump($user);*/

		$db =& App::db('Users');
		$db->setFetchMode(DBDriver::FETCH_ACT_OBJ);
		$user = $db->fetchAll(array('select' => '*', 'from' => 'users'), null, 'id');
		var_dump($user);


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

		Tag::addDynamicJS('console.log(new Date(SERVER_TIME_NOW));', Tag::AFTER_FOOTER_JS);

	}
}