<?php
session_start();
require 'config.php';
require 'common.php';
if ($_user = session('user')) {
	$_p = get('_p');
	switch ($_p) {
		case 'versions':
		case '':
			require 'pages/versions.php';
			break;
		case 'revision':
			require 'pages/revision.php';
			break;
		case 'note':
			require 'pages/note.php';
			break;
		case 'update':
			require 'pages/update.php';
			break;
		case 'revert':
			require 'pages/revert.php';
			break;
		case 'logs':
			require 'pages/logs.php';
			break;
		case 'users':
			require 'pages/users.php';
			break;
		case 'user':
			require 'pages/user.php';
			break;
		case 'delete':
			require 'pages/delete.php';
			break;
		case 'logout':
			require 'pages/logout.php';
			break;
		default:
			header('Location: ' . BASE_URL, false, 302);
	}
} else {
	$_users = users();
	if ($_users)
		require 'pages/login.php';
	else
		require 'pages/start.php';
}