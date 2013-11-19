<?php
$_config = require '../app/config.php';

require APP_DIR . 'App.php';

App::delCache(App::GET('_del_cache', null));

if (isset($_GET['_url'])) App::parseUrl($_GET['_url']);

$_controller = strtolower(App::getVarName('controller', App::$config->defaultController));
$_action = strtolower(App::getVarName('action', App::$config->defaultAction));

if (PHP_CACHE) {
	$_module = App::$module;
	App::$phpCacheFile = PHP_CACHE_DIR . "$_module.$_controller.$_action.php";
	if (file_exists(App::$phpCacheFile)) require App::$phpCacheFile;
}

App::run($_controller, $_action);