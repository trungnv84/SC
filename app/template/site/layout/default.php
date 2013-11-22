<?php defined('ROOT_DIR') || exit;

Tag::unShiftCSS('common.css', 'common', true);

Tag::unShiftCSS('bootstrap/css/bootstrap.min.css', 'bootstrap', true);
//Tag::addCSS('http://getbootstrap.com/2.3.2/assets/css/bootstrap.css');

Tag::unShiftJS('http://getbootstrap.com/2.3.2/assets/js/bootstrap-alert.js');
Tag::unShiftJS('bootstrap/js/bootstrap.min.js', 'bootstrap', true);
Tag::unShiftJS('//code.jquery.com/jquery-1.10.2.min.js', 'jquery', true);

echo '<div class="main">', $__html__main, '</div>';
