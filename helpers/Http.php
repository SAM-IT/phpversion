<?php


namespace app\helpers;

/**
 * Simple helper class that cached URLs.
 * @package app\helpers
 *
 */
class Http
{

    public static function get($url, $cache = 'cache', $duration = 24 * 3600)
    {
        $cache = \Yii::$app->cache;
        $key = __CLASS__ . $url;
        if (false === $result = $cache->get($key)) {
            $cache->set($key, $result = file_get_contents($url), $duration);

        }
        return $result;
    }

}