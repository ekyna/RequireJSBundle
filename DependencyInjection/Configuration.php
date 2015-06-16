<?php

namespace Ekyna\Bundle\RequireJsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class Configuration
 * @package Ekyna\Bundle\RequireJsBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_require_js');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('template')->defaultValue('EkynaRequireJsBundle::require_js.html.twig')->end()

                ->arrayNode('config')
                    ->addDefaultsIfNotSet()
                    ->children()
                        // @see http://requirejs.org/docs/api.html#config-waitSeconds
                        ->integerNode('waitSeconds')
                            ->min(0)
                            ->defaultValue(0)
                        ->end()
                        // @see http://requirejs.org/docs/api.html#config-enforceDefine
                        ->booleanNode('enforceDefine')->end()
                        // @see http://requirejs.org/docs/api.html#config-scriptType
                        ->scalarNode('scriptType')->cannotBeEmpty()->end()
                    ->end()
                ->end()

                ->scalarNode('web_root')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('js_engine')->defaultNull()->end()
                ->scalarNode('build_path')->defaultValue('js/app.min.js')->end()
                ->integerNode('building_timeout')->min(1)->defaultValue(180)->end()
                ->arrayNode('build')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('optimize')
                            ->values(array('uglify', 'uglify2', 'closure', 'closure.keepLines', 'none'))
                            ->defaultValue('uglify2')
                        ->end()
                        ->booleanNode('generateSourceMaps')->defaultFalse()->end()
                        ->booleanNode('preserveLicenseComments')->defaultFalse()->end()
                        ->booleanNode('useSourceUrl')->defaultFalse()->end()
                        ->arrayNode('paths')->addDefaultsIfNotSet()->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->always(function ($value) {
                    if (empty($value['js_engine'])) {
                        $value['js_engine'] = self::getDefaultJsEngine();
                    }
                    return $value;
                })
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return string|null
     */
    public static function getDefaultJsEngine()
    {
        $jsEngines = ['node', 'nodejs', 'rhino'];
        $availableJsEngines = [];
        foreach ($jsEngines as $engine) {
            $jsExists = new ProcessBuilder(array($engine, '-help'));
            $jsExists = $jsExists->getProcess();
            if (isset($_SERVER['PATH'])) {
                $jsExists->setEnv(array('PATH' => $_SERVER['PATH']));
            }
            $jsExists->run();
            if ($jsExists->getErrorOutput() === null) {
                $availableJsEngines[] = $engine;
            }
        }
        return $availableJsEngines ? reset($availableJsEngines) : null;
    }
}
