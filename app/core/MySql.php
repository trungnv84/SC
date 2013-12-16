<?php
defined('ROOT_DIR') || exit;

class MySql extends DBDriver
{
    private static $connections = array();
    private static $currentDatabase = array();

    private $resource = null;
    private $last_query = null;
    private $current_query = null;

    private static function db_set_charset($instance, $charset, $collation)
    {
        $use_set_names = (version_compare(PHP_VERSION, '5.2.3', '>=') && version_compare(mysql_get_server_info(self::$connections[$instance]), '5.0.7', '>=')) ? FALSE : TRUE;
        if ($use_set_names === TRUE) {
            return mysql_query("SET NAMES '" . $charset . "' COLLATE '" . $collation . "'", self::$connections[$instance]);
        } else {
            return mysql_set_charset($charset, self::$connections[$instance]);
        }
    }

    private static function &collect($instance = DB_INSTANCE)
    {
        if (!$instance) $instance = 'default';
        $config = self::getDbConfig($instance, MYSQL_DRIVER_NAME);
        if (!isset(self::$connections[$instance])) {
            $key = self::getDbKey($instance, MYSQL_DRIVER_NAME);
            if (!isset(self::$connections[$key])) {
                if ($config['pconnect']) {
                    self::$connections[$key] = mysql_pconnect($config['hostname'], $config['username'], $config['password']);
                } else {
                    self::$connections[$key] = mysql_connect($config['hostname'], $config['username'], $config['password']);
                }
                if (false === self::$connections[$key]) {
                    App::end("Could not connect: " . mysql_error() . " -> ???//zzz");
                } else {
                    App::addEndEvents(array(
                        'function' => array(MYSQL_DRIVER_NAME, 'closeAll')
                    ));
                }
                if (!self::db_set_charset($key, $config['char_set'], $config['dbcollat'])) {
                    App::end("DB Set charset error: " . mysql_error() . " -> ???//zzz");
                }
            }
            self::$connections[$instance] =& self::$connections[$key];
        }
        if (!isset(self::$currentDatabase[$instance]) || self::$currentDatabase[$instance] != $config['database']) {
            if (!mysql_select_db($config['database'], self::$connections[$instance])) {
                App::end("Database [$config[database]] not exists -> ???//zzz");
            }
            self::$currentDatabase[$instance] = $config['database'];
        }
        return self::$connections[$instance];
    }

    public static function select_db($database, $instance = DB_INSTANCE)
    {
        if (!$instance) $instance = 'default';
        if (self::$currentDatabase[$instance] != $database) {
            if (!mysql_select_db($database, self::$connections[$instance])) {
                App::end("Database [$database] not exists -> ???//zzz");
            }
            self::$currentDatabase[$instance] = $database;
        }
    }

    public static function close($instance = DB_INSTANCE)
    {
        if (isset(self::$connections[$instance])) {
            if (is_resource(self::$connections[$instance])) {
                mysql_close(self::$connections[$instance]);
            }
            unset(self::$connections[$instance]);
        }
    }

    public static function closeAll()
    {
        foreach (self::$connections as $key => &$instance) {
            if (is_resource($instance)) {
                mysql_close(self::$connections[$key]);
            }
            unset(self::$connections[$key]);
        }
        unset($instance);
        self::$connections = array();
    }

    public function query($sql)
    {
        $this->last_query = $sql;
        $connection =& self::collect($this->instance);
        $this->resource = mysql_query($sql, $connection);
        return ($this->resource ? true : false);
    }

    public function fetch()
    {
        if (is_null($this->resource) || $this->resource === false) return false;

        switch ($this->fetch_mode) {
            case self::FETCH_ASSOC:
            default:
                $result = mysql_fetch_assoc($this->resource);
                break;
            case self::FETCH_OBJ:
                $result = mysql_fetch_object($this->resource);
                break;
            case self::FETCH_NUM:
                $result = mysql_fetch_row($this->resource);
                break;
            case self::FETCH_BOTH:
                $result = mysql_fetch_array($this->resource, MYSQL_BOTH);
                break;
            case self::FETCH_ARR_OBJ:
                $result = mysql_fetch_assoc($this->resource);
                $result = new ArrayObject($result, ArrayObject::ARRAY_AS_PROPS);
                break;
            case self::FETCH_ACT_OBJ:
                if (is_null($this->active_class))
                    $result = mysql_fetch_object($this->resource);
                else
                    $result = mysql_fetch_object($this->resource, $this->active_class, array($this->instance, MYSQL_DRIVER_NAME, $this->_pk));
                break;
        }
        return $result;
    }

    public function result()
    {
        //return $this
    }

    /*public function find()
    {
        $connection =& self::collect($this->instance);
    }*/

}