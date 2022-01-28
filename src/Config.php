<?php
namespace IsThereAnyDeal\Config;

use IsThereAnyDeal\Config\Exceptions\ConfigException;

class Config
{
    private static array $config;

    /**
     * @param array<class-string, string> $map
     */
    private static array $sectionMap = [];

    /**
     * @template T
     * @param array<class-string<T>, T> $map
     */
    private static array $configObjects = [];

    public static function loadJsonFile(string $path, ?string $section=null): void {
        $data = json_decode(file_get_contents($path), true);

        if (isset($data['@extend'])) {
            $baseFile = dirname(realpath($path)).DIRECTORY_SEPARATOR.$data['@extend'];
            self::loadJsonFile($baseFile);
        }

        if (is_null($section)) {
            self::$config = array_replace_recursive(self::$config ?? [], $data);
        } else {
            self::$config[$section] = array_replace_recursive(self::$config[$section] ?? [], $data);
        }
    }

    /**
     * @param array<class-string, string> $map
     */
    public static function map(array $map): void {
        self::$sectionMap = $map;
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     * @return T
     * @throws ConfigException
     */
    public static function getConfig(string $configClass): object {
        if (!isset(self::$configObjects[$configClass])) {
            if (!isset(self::$sectionMap[$configClass])) {
                throw new ConfigException("No mapped config section for $configClass");
            }
            $section = self::$sectionMap[$configClass];

            if (!isset(self::$config[$section])) {
                throw new ConfigException("No config data for $configClass");
            }

            self::$configObjects[$configClass] = new $configClass(self::$config[$section]);
        }

        return self::$configObjects[$configClass];
    }
}
