<?php defined('ROOT_DIR') || exit;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo Tag::getHtmlTitle();?></title>
    <?php echo Tag::getHtmlHeader();?>
</head>

<body>
<?php echo $__html_layout;?>
<?php echo Tag::getHtmlFooter();?>
</body>
</html>