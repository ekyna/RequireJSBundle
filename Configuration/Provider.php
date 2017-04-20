<?php

declare(strict_types=1);

namespace Ekyna\Bundle\RequireJsBundle\Configuration;

use Doctrine\Common\Cache\CacheProvider;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Parser;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_merge_recursive;
use function array_replace;
use function dirname;
use function file_get_contents;
use function get_class;
use function is_array;
use function is_dir;
use function is_file;
use function ltrim;
use function realpath;
use function substr;

/**
 * Class Provider
 * @package Ekyna\Bundle\RequireJsBundle\Configuration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Provider
{
    private const CONFIG_CACHE_KEY = 'ekyna_requirejs_config';

    private UrlGeneratorInterface $generator;
    private KernelInterface       $kernel;
    private array                 $config;

    private Parser                    $parser;
    private ?CacheProvider            $cache           = null;
    private ?VersionStrategyInterface $versionStrategy = null;
    private ?array                    $collectedConfig = null;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $generator
     * @param KernelInterface       $kernel
     * @param array                 $config
     */
    public function __construct(UrlGeneratorInterface $generator, KernelInterface $kernel, array $config)
    {
        $this->generator = $generator;
        $this->kernel = $kernel;
        $this->config = $config;
    }

    /**
     * Sets the cache provider.
     *
     * @param CacheProvider $cache
     */
    public function setCache(CacheProvider $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Sets the version strategy.
     *
     * @param VersionStrategyInterface $strategy
     */
    public function setVersionStrategy(VersionStrategyInterface $strategy): void
    {
        $this->versionStrategy = $strategy;
    }

    /**
     * Returns the config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Fetches piece of JS-code with require.js main config from cache
     * or if it was not there - generates and put into a cache
     *
     * @return array
     */
    public function getMainConfig(): array
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
    public function generateMainConfig(): array
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
                $path = ltrim($path, '/');
            }
        }

        if ($this->versionStrategy) {
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
    public function generateBuildConfig(string $configPath): array
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
     * @throws ReflectionException
     */
    public function collectConfigs(): array
    {
        if ($this->collectedConfig) {
            return $this->collectedConfig;
        }

        $this->parser = new Parser();

        $config = $this->config;

        $this->collectFromDirectory($config, $this->kernel->getProjectDir());

        $this->collectFromClass($config, get_class($this->kernel));

        foreach ($this->kernel->getBundles() as $bundle) {
            $this->collectFromClass($config, get_class($bundle));
        }

        return $this->collectedConfig = $config;
    }

    /**
     * @param array  $config
     * @param string $class
     *
     * @throws ReflectionException
     */
    private function collectFromClass(array &$config, string $class): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $reflection = new ReflectionClass($class);
        $directory = dirname($reflection->getFileName());

        if (is_dir($path = $directory . '/config')) {
            $this->collectFromDirectory($config, $path);
        }

        if (is_dir($path = $directory . '/Resources/config')) {
            $this->collectFromDirectory($config, $path);
        }
    }

    /**
     * @param array  $config
     * @param string $directory
     */
    private function collectFromDirectory(array &$config, string $directory): void
    {
        if (is_file($file = $directory . '/requirejs.yaml')) {
            $bundleConfig = $this->parser->parse(file_get_contents(realpath($file)));
            $config = array_merge_recursive($config, $bundleConfig);
        }

        if (is_file($file = $directory . '/requirejs_' . $this->kernel->getEnvironment() . '.yaml')) {
            $bundleConfig = $this->parser->parse(file_get_contents(realpath($file)));
            $config = array_merge_recursive($config, $bundleConfig);
        }
    }
}
