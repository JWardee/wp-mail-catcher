<?php

namespace WpMailCatcher\Models;

class Cache
{
    static private $cache = [];

    static public function get($key)
    {
        return isset(self::$cache[self::getHashedKey($key)]) ? self::$cache[self::getHashedKey($key)] : null;
    }

    static public function set($key, $value)
    {
        self::$cache[self::getHashedKey($key)] = $value;

        /** Return value to allow chaining on $value */
        return $value;
    }

    static public function flush()
    {
        self::$cache = [];
    }

    static private function getHashedKey($entry)
    {
        return md5(serialize($entry));
    }
}
