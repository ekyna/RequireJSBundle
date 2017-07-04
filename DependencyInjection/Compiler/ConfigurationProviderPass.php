<?php

namespace Ekyna\Bundle\RequireJsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ConfigurationProviderPass
 * @package Ekyna\Bundle\RequireJsBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationProviderPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has('assets._version__default')) {
            $providerDef = $container->getDefinition('ekyna_require_js.configuration_provider');
            $providerDef->addMethodCall('setVersionStrategy', [
                new Reference('assets._version__default')
            ]);
        }
    }
}
