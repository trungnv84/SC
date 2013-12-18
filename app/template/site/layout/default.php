<?php defined('ROOT_DIR') || exit;

Tag::addDynamicJS('const SERVER_TIME_NOW = ' . array_sum(explode(' ', MICRO_TIME_NOW)) * 1000 . ';', Tag::BEFORE_HEADER_JS);

Tag::unShiftCSS('common.css', 'common', true);
Tag::unShiftCSS('bootstrap3/css/bootstrap.min.css', 'bootstrap', true);
Tag::unShiftJS('bootstrap3/js/bootstrap.min.js', 'bootstrap', true);
Tag::unShiftJS('js/jquery-2.0.3.min.js', 'bootstrap', true);

//Tag::addCSS('http://getbootstrap.com/2.3.2/assets/css/bootstrap.css');
//Tag::unShiftJS('http://getbootstrap.com/2.3.2/assets/js/bootstrap-alert.js');
//Tag::unShiftJS('//code.jquery.com/jquery-1.10.2.min.js', 'jquery', true);

echo '<div class="main">', $__main_html, '</div>';