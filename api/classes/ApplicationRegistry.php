<?php
Class ApplicationRegistry extends Registry {

    private $values = array();
    private static $instance;

    private function __construct() {
        $this->openSystemConfigFile();
    }

    private static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function get($key) {
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    protected function set($key, $value) {
        $this->values[$key] = $value;
    }

    private function openSystemConfigFile() {
        $filename = CONFIGLOCATION;  //CONFIGLOCATION a constant defined elsewhere
        if (file_exists($filename)) {
            $temp = simplexml_load_file($filename);
            foreach ($temp as $key => $value) {
                $this->set($key, trim($value));
            }
        }
    }

    public static function getDBName() {
        return self::instance()->get('dbname');
    }

    public static function getSecretKey() {
        return self::instance()->get('secretkey');
    }

    public static function getUsername() {
        return self::instance()->get('username');
    }

    public static function getEmail() {
        return self::instance()->get('email');
    }

    public static function getPassword() {
        return self::instance()->get('password');
    }

    public static function DB() {
        return pdoDB::getConnection();
    }

}
?>
