<?php
defined('GIT_PATH') || exit;
session('user', false);
header("Location: " . BASE_URL, false, 302);