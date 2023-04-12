<?php


namespace TestCommon;


class BrowserConf
{
    static $confRootPath;

    public static function get($confFile)
    {
        if (empty(self::$confRootPath))
        {
            self::$confRootPath = __DIR__ . '/../conf';
        }
        return JIX::get(self::$confRootPath . '/' . $confFile);
    }

    public static function getProfile($capabilities)
    {
        if (!empty($capabilities))
        {
            if (!empty($capabilities['moz:firefoxOptions']))
            {
                return $capabilities['moz:firefoxOptions']['profile'];
            }
            return null;
        }
        return null;
    }
    public static function getBrowserName($capabilities) {
        return $capabilities['browserName'];
    }

    public static function getBrowserArgs($capabilities)
    {
        if (!empty($capabilities))
        {
            if (!empty($capabilities['moz:firefoxOptions']))
            {
                return $capabilities['moz:firefoxOptions']['args'];
            }
            return [];
        }
        return [];
    }

    public static function getNonConfiguredProfileMessage()
    {
        return "Path to browser profile should be set in file " . self::$confRootPath . "/default.btc";
    }
}