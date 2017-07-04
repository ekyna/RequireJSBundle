<?php

namespace Ekyna\Bundle\RequireJsBundle;

use Ekyna\Bundle\RequireJsBundle\DependencyInjection\Compiler\ConfigurationProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaRequireJsBundle
 * @package Ekyna\Bundle\RequireJsBundle
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaRequireJsBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationProviderPass());
    }
}
