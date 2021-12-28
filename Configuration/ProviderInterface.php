<?php

declare(strict_types=1);

namespace Ekyna\Bundle\RequireJsBundle\Configuration;

/**
 * Class Provider
 * @package Ekyna\Bundle\RequireJsBundle\Configuration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ProviderInterface
{
    /**
     * Returns the config.
     */
    public function getConfig(): array;

    /**
     * Fetches piece of JS-code with require.js main config from cache
     * or if it was not there - generates and put into a cache
     */
    public function getMainConfig(): array;

    /**
     * Generates main config for require.js
     */
    public function generateMainConfig(): array;

    /**
     * Generates build config for require.js
     *
     * @param string $configPath path to require.js main config
     */
    public function generateBuildConfig(string $configPath): array;
}
