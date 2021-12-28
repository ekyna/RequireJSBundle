<?php

declare(strict_types=1);

namespace Ekyna\Bundle\RequireJsBundle\Command;

use Ekyna\Bundle\RequireJsBundle\Configuration\ProviderInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class BuildCommand
 * @package Ekyna\Bundle\RequireJsBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BuildCommand extends Command
{
    protected static $defaultName = 'ekyna:requirejs:build';

    private const MAIN_CONFIG_FILE_NAME  = 'js/require-config.js';
    private const BUILD_CONFIG_FILE_NAME = 'build.js';
    private const OPTIMIZER_FILE_PATH    = 'node_modules/requirejs/bin/r.js';

    private ProviderInterface $provider;


    public function __construct(ProviderInterface $provider)
    {
        parent::__construct();

        $this->provider = $provider;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Build single optimized js resource')
            ->addOption('optimizer', 'o', InputOption::VALUE_NONE, 'Whether or not to run r.js optimizer.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->provider->getConfig();
        $webRoot = realpath($config['web_root']);

        $output->writeln('Generating require.js main config');
        $jsonConfig = json_encode($this->provider->generateMainConfig());
        // for some reason built application gets broken with configuration in "oneline-json"
        $mainConfigContent = "require(\n" . $jsonConfig . "\n);";
        $mainConfigContent = str_replace(',', ",\n", $mainConfigContent);
        $mainConfigFilePath = $webRoot . DIRECTORY_SEPARATOR . self::MAIN_CONFIG_FILE_NAME;
        $mainConfigDirectory = dirname($mainConfigFilePath);
        if (!is_dir($mainConfigDirectory)) {
            if (!mkdir($mainConfigDirectory)) {
                throw new RuntimeException('Unable to create directory ' . $mainConfigDirectory);
            }
        }
        if (false === file_put_contents($mainConfigFilePath, $mainConfigContent)) {
            throw new RuntimeException('Unable to write file ' . $mainConfigFilePath);
        }

        $output->writeln('Generating require.js build config');
        $buildConfigContent = $this->provider->generateBuildConfig(self::MAIN_CONFIG_FILE_NAME);
        $buildConfigContent = '(' . json_encode($buildConfigContent) . ')';
        $buildConfigFilePath = $webRoot . DIRECTORY_SEPARATOR . self::BUILD_CONFIG_FILE_NAME;
        if (false === file_put_contents($buildConfigFilePath, $buildConfigContent)) {
            throw new RuntimeException('Unable to write file ' . $buildConfigFilePath);
        }

        if (!$input->getOption('optimizer')) {
            $output->writeln('You can now run "r.js -o public/build.js".');

            return Command::SUCCESS;
        }

        if (isset($config['js_engine']) && $config['js_engine']) {
            $output->writeln('Running code optimizer');
            $command = [
                $config['js_engine'],
                self::OPTIMIZER_FILE_PATH,
                '-o' .
                $buildConfigFilePath
            ]; // . ' 1>&2';
            $process = new Process($command, $webRoot);
            $process->setTimeout($config['building_timeout']);
            // some workaround when this command is launched from web
            if (isset($_SERVER['PATH'])) {
                $env = $_SERVER;
                if (isset($env['Path'])) {
                    unset($env['Path']);
                }
                $process->setEnv($env);
            }
            $process->run();
            if (!$process->isSuccessful()) {
                $output->writeln($command);
                $output->writeln($process->getOutput());
                throw new RuntimeException($process->getErrorOutput());
            }

            $output->writeln(
                sprintf(
                    '<comment>%s</comment> <info>[file+]</info> %s',
                    date('H:i:s'),
                    realpath($webRoot . DIRECTORY_SEPARATOR . $config['build_path'])
                )
            );
        } else {
            $output->writeln('No engine configured.');
        }

        $output->writeln('Cleaning up');
        if (false === unlink($buildConfigFilePath)) {
            throw new RuntimeException('Unable to remove file ' . $buildConfigFilePath);
        }

        return Command::SUCCESS;
    }
}
