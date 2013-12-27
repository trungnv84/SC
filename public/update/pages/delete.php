<?php
defined('GIT_PATH') || exit;

if (!$_user['root'])
	header('Location: ' . BASE_URL, false, 302);

$_users = users();
$username = get('username');
$id = base64_encode(strtolower($username));
if (isset($_users[$id]) && !$_users[$id]['root']) {
	unset($_users[$id]);
	users($_users);
}

header('Location: ' . BASE_URL . '?_p=users', false, 302);