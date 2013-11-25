<?php
$config = new stdClass();

$config->autoLoadPath = array(
	CORE_DIR,
	'Controller' => CONTROLLER_DIR,
	/*'View' => APP_DIR . DS . 'view' . DS,*/
	'Model' => MODEL_DIR,
	LIBRARY_DIR
);

$config->router = array(
	/*array(
		'/admin(/|/([^/\.]+))?(/|/([^/\.]+))?(/|\\' . REWRITE_SUFFIX . ')?',
		array('controller' => 2, 'action' => 4)
	),
	array(
		'/_tools(/|/([^/\.]+))?(/|/([^/\.]+))?(/|\\' . REWRITE_SUFFIX . ')?',
		array('controller' => 2, 'action' => 4)
	),*/
	array(
		'/([^/\.]+)(/|/([^/\.]+))?(/|\\' . REWRITE_SUFFIX . ')?',
		array('controller' => 1, 'action' => 3)
	)
);

$config->modules = array('site', 'admin', '_tools');

$config->modulePaths = array(
	'site' => array(
		'Controller' => $config->autoLoadPath['Controller'] . 'site' . DS //CONTROLLER_DIR
	),
	'admin' => array(
		'Controller' => $config->autoLoadPath['Controller'] . 'admin' . DS //CONTROLLER_DIR
	),
	'_tools' => array(
		'Controller' => $config->autoLoadPath['Controller'] . '_tools' . DS //CONTROLLER_DIR
	)
);

$config->moduleTemplates = array(
	'site' => 'site',
	'admin' => 'admin',
	'_tools' => '_tools'
);

/*##########################################################*/

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$config->db[MYSQL_DRIVER_NAME] = array(
	'hostname' => '172.16.90.26',
	'username' => 'admin',
	'password' => 'admin',
	'database' => 'tivitz',
	'dbprefix' => 'tbl_',
	'pconnect' => true,
	//'db_debug' => TRUE;
	//'cache_on' => FALSE;
	//'cachedir' => '';
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	//'swap_pre' => '';
	//'autoinit' => TRUE;
	//'stricton' => FALSE;
);

if (DB_INSTANCE) {
	$config->db = array(
		DB_INSTANCE => $config->db
	);

	$config->db['user'] = array(
		MYSQL_DRIVER_NAME => array(
			'hostname' => '172.16.90.26',
			'username' => 'admin',
			'password' => 'admin',
			'database' => 'tivitz',
			'dbprefix' => '',
			'pconnect' => false,
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci'
		)
	);

	$config->db['session'] = array(
		MYSQL_DRIVER_NAME => 'user.sc_session'
	);
}

/*##########################################################*/

$config->dbKeyIgnores = array(
	MYSQL_DRIVER_NAME => array('database', 'dbprefix')
);

/*##########################################################*/

$config->defaultModule = DEFAULT_MODULE;
$config->defaultController = DEFAULT_CONTROLLER;
$config->defaultAction = DEFAULT_ACTION;

return $config;