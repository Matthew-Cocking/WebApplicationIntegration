<?php
Abstract Class Registry {
    private function __construct() {}
    abstract protected function get($key);
    abstract protected function set($key, $value);
}
?>
