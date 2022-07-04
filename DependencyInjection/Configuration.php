<?php

declare(strict_types=1);

namespace Ekyna\Bundle\RequireJsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Process\Process;

/**
 * Class Configuration
 * @package Ekyna\Bundle\RequireJsBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ekyna_require_js');

        $root = $builder->getRootNode();

        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('template')->defaultValue('@EkynaRequireJs/require_js.html.twig')->end()
                ->scalarNode('asset_strategy')->defaultNull()->end()

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
                        ->scalarNode('baseUrl')->defaultValue('/')->cannotBeEmpty()->end()
                    ->end()
                ->end()

                ->scalarNode('web_root')->defaultValue('%kernel.project_dir%/public')->end()
                ->scalarNode('js_engine')->defaultNull()->end()
                ->scalarNode('build_path')->defaultValue('js/app.min.js')->end()
                ->integerNode('building_timeout')->min(1)->defaultValue(180)->end()
                ->arrayNode('build')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('optimize')
                            ->values(['uglify', 'uglify2', 'closure', 'closure.keepLines', 'none'])
                            ->defaultValue('none')
                        ->end()
                        ->booleanNode('generateSourceMaps')->defaultFalse()->end()
                        ->booleanNode('preserveLicenseComments')->defaultFalse()->end()
                        ->booleanNode('useSourceUrl')->defaultFalse()->end()
                        ->arrayNode('paths')->addDefaultsIfNotSet()->end()
                        ->scalarNode('baseUrl')->defaultValue('./')->cannotBeEmpty()->end()
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

        return $builder;
    }

    /**
     * @return string|null
     */
    public static function getDefaultJsEngine(): ?string
    {
        $engines = ['node', 'nodejs', 'rhino'];
        $available = [];

        foreach ($engines as $engine) {
            $exists = new Process([$engine, '-help']);
            if (isset($_SERVER['PATH'])) {
                $exists->setEnv(['PATH' => $_SERVER['PATH']]);
            }
            $exists->run();
            if (empty($exists->getErrorOutput())) {
                $available[] = $engine;
            }
        }

        return $available ? reset($available) : null;
    }
}
