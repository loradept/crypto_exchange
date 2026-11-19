<?php
class DB {
    private static $instance = null;

    public static function getInstance() {
        if (!self::$instance) {
            // Данные: хост, юзер (root), пароль (обычно пустой), имя базы (crypto_db)
            self::$instance = new mysqli('localhost', 'root', '', 'crypto_db');
            
            if (self::$instance->connect_error) {
                die("Ошибка подключения к БД: " . self::$instance->connect_error);
            }
        }
        return self::$instance;
    }
}