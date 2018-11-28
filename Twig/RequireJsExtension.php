<?php

namespace Ekyna\Bundle\RequireJsBundle\Twig;

use Ekyna\Bundle\RequireJsBundle\Configuration\Provider;

/**
 * Class RequireJsExtension
 * @package Ekyna\Bundle\RequireJsBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RequireJsExtension extends \Twig_Extension
{
    /**
     * @var Provider
     */
    protected $provider;


    /**
     * Constructor.
     *
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'require_js',
                [$this, 'renderRequireJs'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * Renders the RequireJs initialisation script.
     *
     * @param \Twig_Environment $env
     * @param bool              $compressed
     *
     * @return string
     */
    public function renderRequireJs(\Twig_Environment $env, $compressed = true)
    {
        $config = $this->provider->getConfig();

        if ($compressed && !file_exists($config['web_root'] . DIRECTORY_SEPARATOR . $config['build_path'])) {
            throw new \RuntimeException("Build file does not exists.");
        }

        return $env->render($config['template'], [
            'compressed' => $compressed,
            'build_path' => $config['build_path'],
            'config'     => $this->provider->getMainConfig(),
        ]);
    }
}
