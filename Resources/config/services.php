<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\RequireJsBundle\Command\BuildCommand;
use Ekyna\Bundle\RequireJsBundle\Configuration\Provider;
use Ekyna\Bundle\RequireJsBundle\Configuration\Renderer;
use Ekyna\Bundle\RequireJsBundle\Twig\RequireJsExtension;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Configuration provider
        ->set('ekyna_require_js.configuration.provider', Provider::class)
            ->args([
                service('router'),
                service('kernel'),
                abstract_arg('RequireJs configuration'),
            ])

        // Configuration renderer
        ->set('ekyna_require_js.configuration.renderer', Renderer::class)
            ->args([
                service('ekyna_require_js.configuration.provider'),
                service('twig'),
            ])
            ->tag('twig.runtime')

        // Twig extension
        ->set('ekyna_require_js.twig.extension', RequireJsExtension::class)
            ->tag('twig.extension')

        // Build command
        ->set('ekyna_require_js.command.build', BuildCommand::class)
            ->args([
                service('ekyna_require_js.configuration.provider')
            ])
            ->tag('console.command')
    ;
};
