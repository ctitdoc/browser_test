<?php

namespace TestCommon;


class TestFixtures
{
    static $fixturesRootPath;

    private static function setPath()
    {
        if (empty(self::$fixturesRootPath))
        {
            self::$fixturesRootPath = __DIR__ . '/../data/fixtures';
        }
    }
    public static function get($fixturesFile)
    {
        self::setPath();
        return JIX::get(self::$fixturesRootPath . '/' . $fixturesFile);
    }

    public static function getContent($fixturesFile)
    {
        self::setPath();
        return file_get_contents(self::$fixturesRootPath . '/' . $fixturesFile);
    }

    public static function getJson($fixturesFile)
    {
        self::setPath();
        return json_decode(file_get_contents(self::$fixturesRootPath . '/' . $fixturesFile),true);
    }

    public static function getFilePath($fixturesFile)
    {
        self::setPath();
        return self::$fixturesRootPath . '/' . $fixturesFile;
    }

    public static function getNonConfiguredEmailMessage()
    {
        return "Email value should be set in file " . self::$fixturesRootPath . "/common.ent";
    }

}