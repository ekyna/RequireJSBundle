<?php

namespace Ekyna\Bundle\RequireJsBundle\Configuration;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;


/**
 * Class Provider
 * @package Ekyna\Bundle\RequireJsBundle\Configuration
 * @author Étienne Dauvergne <contact@ekyna.com>
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
        $this->config    = $config;
        $this->bundles   = $bundles;
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
        $requirejs = $this->collectConfigs();
        $config = $requirejs['config'];
        if (!empty($config['paths']) && is_array($config['paths'])) {
            foreach ($config['paths'] as &$path) {
                if (is_array($path)) {
                    $path = $this->generator->generate($path['route'], $path['params'], UrlGeneratorInterface::ABSOLUTE_URL);
                }
                if (substr($path, -3) === '.js') {
                    $path = substr($path, 0, -3);
                }
            }
        }
        $config['baseUrl'] = '/';
        return $config;
    }

    /**
     * Generates build config for require.js
     *
     * @param string $configPath path to require.js main config
     * @return array
     */
    public function generateBuildConfig($configPath)
    {
        $config = $this->collectConfigs();
        $config['build']['baseUrl'] = './';
        $config['build']['out'] = './' . $config['build_path'];
        $config['build']['mainConfigFile'] = './' . $configPath;
        $paths = array(
            // build-in configuration
            'require-config' => './' . substr($configPath, 0, -3),
            // build-in require.js lib
            'require-lib' => 'bundles/ekynarequirejs/require',
        );
        $config['build']['paths'] = array_merge($config['build']['paths'], $paths);
        $config['build']['include'] = array_merge(
            array_keys($paths),
            array_keys($config['config']['paths'])
        );
        return $config['build'];
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
                if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/requirejs.yml')) {
                    $bundleConfig = Yaml::parse(realpath($file));
                    $config = array_merge_recursive($config, $bundleConfig);
                }
            }
            $this->collectedConfig = $config;
        }
        return $this->collectedConfig;
    }
}
