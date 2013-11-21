<?php
ob_start();

require '../app/constant.php';

require APP_DIR . 'App.php';

App::run();