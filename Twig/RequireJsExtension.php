<?php

declare(strict_types=1);

namespace Ekyna\Bundle\RequireJsBundle\Twig;

use Ekyna\Bundle\RequireJsBundle\Configuration\Renderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class RequireJsExtension
 * @package Ekyna\Bundle\RequireJsBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RequireJsExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'require_js',
                [Renderer::class, 'render'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
