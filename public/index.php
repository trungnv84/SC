<?php
ob_start();

require '../app/constant.php';

require APP_DIR . 'App.php';

App::showError((int)App::GET('_show_error', 0));

App::delCache(App::getVarAlNum('_del_cache'));

if (isset($_GET['_url'])) App::parseUrl($_GET['_url']);

$_controller = strtolower(App::getVarName('controller', App::$config->defaultController));
$_action = strtolower(App::getVarName('action', App::$config->defaultAction));

if (PHP_CACHE) {
	$_module = App::$module;
	App::$phpCacheFile = PHP_CACHE_DIR . "$_module.$_controller.$_action.php";
	if (file_exists(App::$phpCacheFile)) require App::$phpCacheFile;
}

App::run($_controller, $_action);