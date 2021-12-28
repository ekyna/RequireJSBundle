<?php

declare(strict_types=1);

namespace Ekyna\Bundle\RequireJsBundle\Configuration;

/**
 * Class Renderer
 * @package Ekyna\Bundle\RequireJsBundle\Configuration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface RendererInterface
{
    /**
     * Renders the require js initialisation script.
     */
    public function render(bool $compressed = true): string;
}
