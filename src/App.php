<?php
namespace MonitoLib;

class App
{
    static private $debug = 0;
    static private $instance;
    static private $isWindows;
    static private $cachePath;
    static private $configPath;
    static private $logPath;
    static private $rootPath;
    static private $rootUrl;
    static private $storagePath;
    static private $tmpPath;

    private function __construct ()
    {
    }
    public static function getDebug ()
    {
        return self::$debug;
    }
    public static function getInstance ()
    {
        if (!isset(self::$instance)) {
            self::$instance = new \MonitoLib\App;
        }

        return self::$instance;
    }
    private static function getPath ($directory, $relativePath = null)
    {
        $directoryPath = $directory . 'Path'; 

        if (is_null(self::$$directoryPath)) {
            $path = self::$rootPath . $directory . DIRECTORY_SEPARATOR;

            if (!file_exists($path)) {
                if (!mkdir($path, 0755, true)) {
                    throw new \Exception("Error creating directory $path", 1);
                }
            }

            self::$$directoryPath = $path;
        }

        if (!is_null($relativePath)) {
            $relativePath = self::$$directoryPath . $relativePath . DIRECTORY_SEPARATOR;

            if (!file_exists($relativePath)) {
                if (!mkdir($relativePath, 0755, true)) {
                    throw new \Exception("Error creating directory $relativePath", 1);
                }
            }

            return $relativePath;
        }

        return self::$$directoryPath;
    }
    public static function getCachePath ($relativePath = null)
    {
        return self::getPath('cache', $relativePath);
    }
    public static function getConfigPath ($relativePath = null)
    {
        return self::getPath('config', $relativePath);
    }
    public static function getLogPath ($relativePath = null)
    {
        return self::getPath('log', $relativePath);
    }
    public static function getStoragePath ($relativePath = null)
    {
        return self::getPath('storage', $relativePath);
    }
    public static function getRootPath ()
    {
        return self::$rootPath;
    }
    public static function getRootUrl ()
    {
        return self::$rootUrl;
    }
    public static function getTmpPath ($relativePath = null)
    {
        return self::getPath('tmp', $relativePath);
    }
    public static function isWindows ()
    {
        return self::$isWindows;
    }
    public static function now () {
        return date('Y-m-d H:i:s');
    }
    public static function setDebug ($debug)
    {
        if (!is_integer($debug) || $debug < 0 || $debug > 2) {
            throw new \MonitoLib\Exception\InternalError('Wrong debug level: value must be 0, 1 or 2!');
        }

        self::$debug = $debug;
    }
    public static function setRootPath ($rootPath)
    {
        if (is_null(self::$rootPath)) {
            self::$rootPath = $rootPath;
        }
    }
    public static function setRootUrl ($rootUrl)
    {
        if (is_null(self::$rootUrl)) {
            self::$rootUrl = $rootUrl;
        }
    }
}