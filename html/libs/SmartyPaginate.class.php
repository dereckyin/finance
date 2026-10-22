<?php

class SmartyPaginate
{
    private static $limit = 25;
    private static $total = 0;
    private static $currentIndex = 0;
    private static $pageVar = 'page';
    private static $limitVar = 'pagesize';

    public static function connect()
    {
        $page = isset($_GET[self::$pageVar]) ? (int)$_GET[self::$pageVar] : 1;
        $limit = isset($_GET[self::$limitVar]) ? (int)$_GET[self::$limitVar] : self::$limit;
        if ($limit <= 0) {
            $limit = self::$limit;
        }
        if ($page <= 0) {
            $page = 1;
        }
        self::$limit = $limit;
        self::$currentIndex = ($page - 1) * self::$limit;
    }

    public static function setLimit($limit)
    {
        if ((int)$limit > 0) {
            self::$limit = (int)$limit;
        }
    }

    public static function getLimit()
    {
        return self::$limit;
    }

    public static function getCurrentIndex()
    {
        return self::$currentIndex;
    }

    public static function setTotal($total)
    {
        self::$total = max(0, (int)$total);
    }

    public static function getTotalPages()
    {
        if (self::$limit <= 0) {
            return 1;
        }
        return (int)max(1, ceil(self::$total / self::$limit));
    }

    public static function getCurrentPage()
    {
        if (self::$limit <= 0) {
            return 1;
        }
        return (int)floor(self::$currentIndex / self::$limit) + 1;
    }

    public static function assign($smarty)
    {
        // No-op for compatibility. Plugins will use static getters.
        return true;
    }
}


