<?php
session_start();
require 'config.php';
require 'common.php';
if (isset($_SESSION['user'])) {
    $_a = isset($_GET['a']) ? $_GET['a'] : null;
    switch ($_a) {
        case 'note':
            require 'note.php';
            break;
        case 'update':
            require 'update.php';
            break;
        case 'version':
        default:
            require 'version.php';
            break;
    }
} else {
    require 'login.php';
}