<?php


namespace daemionfox\Traits;


use daemionfox\Exceptions\PatreonCacheException;

trait CacheTrait
{
    protected static $alllowCache = false;
    protected static $cacheDir;
    protected static $cacheTimeout = 14400; // 4 hours


    /**
     * @return mixed
     * @throws PatreonCacheException
     */
    protected function getCache($file)
    {
        if (self::$alllowCache === true) {
            if (empty(self::$cacheDir)) {
                throw new PatreonCacheException("Cache dir not set");
            }
            if (!is_dir(self::$cacheDir)) {
                throw new PatreonCacheException("Cache dir does not exist");
            }

            if (empty($file)) {
                throw new PatreonCacheException("Cache file not set");
            }
            $cacheDir = self::$cacheDir;
            $cachePath = "{$cacheDir}/{$file}";
            if (!file_exists($cachePath)) {
                throw new PatreonCacheException("Cache file does not exist");
            }

            $filemtime = filemtime($cachePath);
            $now = time();
            if ($filemtime <= ($now - self::$cacheTimeout)) {
                throw new PatreonCacheException("Cache file too old");
            }

            $data = json_decode(file_get_contents($cachePath), true);
            if (empty($data)) {
                throw new PatreonCacheException("No data found");
            }

            return $data;
        }
        throw new PatreonCacheException("Cache is not active");
    }

    /**
     * @param $data
     * @throws PatreonCacheException
     */
    protected function saveCache($file, $data)
    {

        if (self::$alllowCache === true) {
            if(empty($file)) {
                throw new PatreonCacheException("Cache file not set");
            }
            if (empty(self::$cacheDir)) {
                throw new PatreonCacheException("Cache dir not set");
            }
            if (!is_dir(self::$cacheDir)) {
                throw new PatreonCacheException("Cache dir does not exist");
            }
            $cacheDir = self::$cacheDir;

            $cachePath = "{$cacheDir}/{$file}";
            file_put_contents($cachePath, json_encode($data, JSON_PRETTY_PRINT));
        }
        throw new PatreonCacheException("Cache is not active");
    }

    /**
     * @param bool $alllowCache
     */
    public static function setAlllowCache(bool $allowCache): void
    {
        self::$alllowCache = $allowCache;
    }

    /**
     * @param mixed $cacheDir
     */
    public static function setCacheDir($cacheDir): void
    {
        self::$cacheDir = $cacheDir;
    }

    /**
     * @param int $cacheTimeout
     */
    public static function setCacheTimeout(int $cacheTimeout): void
    {
        self::$cacheTimeout = $cacheTimeout;
    }



}