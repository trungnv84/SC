<?php
namespace Joomla;

define('JPATH_PLATFORM', true);

function getFileNameAutoLoad($class_name)
{
	if (strpos($class_name, '\\') === 0) $class_name = substr($class_name, 1);
	$class_name = preg_replace('/(([A-Z][a-zA-Z0-9]+)\\\)|(J?([A-Z][a-z0-9]+))/', '$2$4' . DS, $class_name);
	$class_name = strtolower(substr($class_name, 0, -1));
	$class_file = __DIR__ . DS . '..' . DS . $class_name;
	$adapter_file = $class_file . '.adt.php';
	$class_name .= '.adt';
	if (!file_exists($adapter_file)) {
		$class_file .= '.php';
		if (file_exists($class_file)) {
			file_put_contents($adapter_file,
				preg_replace('/defined\(\'JPATH_PLATFORM\'\)\s*or\s*(exit|die)\s*(\(\s*\))?\s*;/', '',
					str_replace(
						'<?php', "<?php\nnamespace Joomla {",
						file_get_contents($class_file)
					)
				) . "\n}\n"
			);
		}
	}
	return $class_name;
}

function getClassNameAutoLoad($class_name)
{
	return $class_name;
}