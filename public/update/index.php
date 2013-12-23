<?php
session_start();
require 'config.php';
require 'common.php';
if (session('user')) {
	$_p = get('_p');
	switch ($_p) {
		case 'version':
		default:
			require 'pages/version.php';
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
		case 'db':
			require 'pages/db.php';
			break;
	}
} else {
	require 'pages/login.php';
}