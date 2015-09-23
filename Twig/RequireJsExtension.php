<?php

namespace Ekyna\Bundle\RequireJsBundle\Twig;

use Ekyna\Bundle\RequireJsBundle\Configuration\Provider;

/**
 * Class RequireJsExtension
 * @package Ekyna\Bundle\RequireJsBundle\Twig
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class RequireJsExtension extends \Twig_Extension
{
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Twig_Template
     */
    protected $template;


    /**
     * Constructor.
     *
     * @param Provider $provider
     * @param array    $config
     */
    public function __construct(Provider $provider, array $config)
    {
        $this->provider = $provider;
        $this->config   = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        if (!$this->template instanceof \Twig_Template) {
            $this->template = $environment->loadTemplate($this->config['template']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('require_js', [$this, 'renderRequireJs'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Renders the RequireJs initialisation script.
     *
     * @param bool $compressed
     * @return string
     */
    public function renderRequireJs($compressed = true)
    {
        if ($compressed && !$this->buildFileExists()) {
            // TODO throw exception ?
            $compressed = false;
        }

        return $this->template->render([
            'compressed' => $compressed,
            'build_path' => $this->config['build_path'],
            'config'     => $this->provider->getMainConfig(),
        ]);
    }

    /**
     * Checks if the build file exists.
     *
     * @return bool
     */
    private function buildFileExists()
    {
        return file_exists($this->config['web_root'] . DIRECTORY_SEPARATOR . $this->config['build_path']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_require_js';
    }
}
