<?php
namespace IsThereAnyDeal\Config;

use IsThereAnyDeal\Config\Exceptions\ConfigException;

class Config
{
    private array $config;

    /**
     * @param array<class-string, string> $map
     */
    private array $sectionMap = [];

    /**
     * @template T
     * @param array<class-string<T>, T> $map
     */
    private array $configObjects = [];

    public function loadJsonFile(string $path, ?string $section=null): void {
        $data = json_decode(file_get_contents($path), true);

        if (isset($data['@extend'])) {
            $baseFile = dirname(realpath($path)).DIRECTORY_SEPARATOR.$data['@extend'];
            $this->loadJsonFile($baseFile);
        }

        if (is_null($section)) {
            $this->config = array_replace_recursive($this->config ?? [], $data);
        } else {
            $this->config[$section] = array_replace_recursive($this->config[$section] ?? [], $data);
        }
    }

    /**
     * @param array<class-string, string> $map
     */
    public function map(array $map): void {
        $this->sectionMap = $map;
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     * @return T
     * @throws ConfigException
     */
    public function getConfig(string $configClass): object {
        if (!isset($this->configObjects[$configClass])) {
            if (!isset($this->sectionMap[$configClass])) {
                throw new ConfigException("No mapped config section for $configClass");
            }
            $section = $this->sectionMap[$configClass];

            if (!isset($this->config[$section])) {
                throw new ConfigException("No config data for $configClass");
            }

            $this->configObjects[$configClass] = new $configClass($this->config[$section]);
        }

        return $this->configObjects[$configClass];
    }
}
