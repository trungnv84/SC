<?php
function get($name, $default = null)
{
	if (isset($_POST[$name])) return $_POST[$name];
	if (isset($_GET[$name])) return $_GET[$name];
	return $default;
}

function session($name, $value = null)
{
	if (isset($_SESSION['_UPDATE_TOOL'][$name])) $old = $_SESSION['_UPDATE_TOOL'][$name];
	else $old = $value;
	if (isset($value))
		$_SESSION['_UPDATE_TOOL'][$name] = $value;
	return $old;
}