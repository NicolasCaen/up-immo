<?php
namespace UpImmo\Core;

abstract class Singleton {
    private static $instances = [];

    protected function __construct() {}
    protected function __clone() {}

    public static function getInstance(): self {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }
} 