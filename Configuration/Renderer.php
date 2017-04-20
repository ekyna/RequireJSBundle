<?php

declare(strict_types=1);

namespace Ekyna\Bundle\RequireJsBundle\Configuration;

use RuntimeException;
use Twig\Environment;

use function file_exists;

use const DIRECTORY_SEPARATOR;

/**
 * Class Renderer
 * @package Ekyna\Bundle\RequireJsBundle\Configuration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Renderer
{
    private Provider $provider;
    private Environment $twig;

    public function __construct(Provider $provider, Environment $twig)
    {
        $this->provider = $provider;
        $this->twig = $twig;
    }

    /**
     * Renders the require js initialisation script.
     */
    public function render(bool $compressed = true): string
    {
        $config = $this->provider->getConfig();

        if ($compressed && !file_exists($config['web_root'] . DIRECTORY_SEPARATOR . $config['build_path'])) {
            throw new RuntimeException('Build file does not exists.');
        }

        return $this->twig->render($config['template'], [
            'compressed' => $compressed,
            'build_path' => $config['build_path'],
            'config'     => $this->provider->getMainConfig(),
        ]);
    }
}
