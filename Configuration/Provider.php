<?php

namespace Ekyna\Bundle\RequireJsBundle\Configuration;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Provider
 * @package Ekyna\Bundle\RequireJsBundle\Configuration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Provider
{
    const CONFIG_CACHE_KEY = 'ekyna_requirejs_config';

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var CacheProvider
     */
    private $cache;

    /**
     * @var VersionStrategyInterface
     */
    private $versionStrategy;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $bundles;

    /**
     * @var array
     */
    private $collectedConfig;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $generator
     * @param array                 $config
     * @param array                 $bundles
     */
    public function __construct(UrlGeneratorInterface $generator, array $config, array $bundles)
    {
        $this->generator = $generator;
        $this->config = $config;
        $this->bundles = $bundles;
    }

    /**
     * Sets the cache provider.
     *
     * @param \Doctrine\Common\Cache\CacheProvider $cache
     */
    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Sets the version strategy.
     *
     * @param VersionStrategyInterface $strategy
     */
    public function setVersionStrategy(VersionStrategyInterface $strategy)
    {
        $this->versionStrategy = $strategy;
    }

    /**
     * Returns the config.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Fetches piece of JS-code with require.js main config from cache
     * or if it was not there - generates and put into a cache
     *
     * @return string
     */
    public function getMainConfig()
    {
        $config = null;
        if ($this->cache) {
            $config = $this->cache->fetch(self::CONFIG_CACHE_KEY);
        }
        if (empty($config)) {
            $config = $this->generateMainConfig();
            if ($this->cache) {
                $this->cache->save(self::CONFIG_CACHE_KEY, $config);
            }
        }

        return $config;
    }

    /**
     * Generates main config for require.js
     *
     * @return array
     */
    public function generateMainConfig()
    {
        $config = $this->collectConfigs()['config'];

        $config = array_replace([
            'baseUrl' => '/',
        ], $config);

        if (!empty($config['paths']) && is_array($config['paths'])) {
            foreach ($config['paths'] as &$path) {
                if (is_array($path)) {
                    $path = $this->generator->generate(
                        $path['route'],
                        array_key_exists('params', $path) ? $path['params'] : [],
                        UrlGeneratorInterface::ABSOLUTE_PATH
                    );
                }
                if (substr($path, -3) === '.js') {
                    $path = substr($path, 0, -3);
                }
            }
        }

        if (null !== $this->versionStrategy) {
            $config['urlArgs'] = ltrim($this->versionStrategy->applyVersion('/'), '?/');
        }

        return $config;
    }

    /**
     * Generates build config for require.js
     *
     * @param string $configPath path to require.js main config
     *
     * @return array
     */
    public function generateBuildConfig($configPath)
    {
        $all = $this->collectConfigs();

        $config = array_replace([
            'baseUrl'        => './',
            'out'            => './' . $all['build_path'],
            'mainConfigFile' => './' . $configPath,
        ], $all['build']);

        $paths = [
            // build-in configuration
            'require-config' => './' . substr($configPath, 0, -3),
            // build-in require.js lib
            'require-lib'    => 'bundles/ekynarequirejs/require',
        ];
        $config['paths'] = array_merge($config['paths'], $paths);

        $config['include'] = array_merge(
            array_keys($paths),
            //array_keys($config['config']['paths'])
            $config['include']
        );

        return $config;
    }

    /**
     * Goes across bundles and collects configurations
     *
     * @return array
     */
    public function collectConfigs()
    {
        if (!$this->collectedConfig) {
            $config = $this->config;
            foreach ($this->bundles as $bundle) {
                $reflection = new \ReflectionClass($bundle);
                $directory = dirname($reflection->getFileName());
                if (is_file($file = $directory . '/Resources/config/requirejs.yml')) {
                    $bundleConfig = Yaml::parse(file_get_contents(realpath($file)));
                    $config = array_merge_recursive($config, $bundleConfig);
                }
                if (is_file($file = $directory . '/Resources/config/requirejs_' . $this->config['env'] . '.yml')) {
                    $bundleConfig = Yaml::parse(file_get_contents(realpath($file)));
                    $config = array_merge_recursive($config, $bundleConfig);
                }
            }
            $this->collectedConfig = $config;
        }

        return $this->collectedConfig;
    }
}
