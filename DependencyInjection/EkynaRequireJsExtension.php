<?php

declare(strict_types=1);

namespace Ekyna\Bundle\RequireJsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class EkynaRequireJsExtension
 * @package Ekyna\Bundle\RequireJsBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaRequireJsExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $strategy = $config['asset_strategy'];
        unset($config['asset_strategy']);

        $provider = $container
            ->getDefinition('ekyna_require_js.configuration.provider')
            ->replaceArgument(2, $config);

        if (!empty($strategy)) {
            $provider->addMethodCall('setVersionStrategy', [new Reference($strategy)]);
        }
    }
}
