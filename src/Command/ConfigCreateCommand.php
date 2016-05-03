<?php

namespace Bolt\Deploy\Command;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;

/**
 * Configuration file creation command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ConfigCreateCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('config:create')
            ->setDescription('Configuration file creation')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('file-name', null, InputOption::VALUE_REQUIRED, 'File name of new configuration file'),
                ])
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getDefaultConfigArray();
        $dumper = new Dumper();
        $yaml = $dumper->dump($config, 6);

        if ($input->getOption('file-name')) {
            $fileName = $input->getOption('file-name');
            if ((new \SplFileInfo($fileName))->getExtension() !== 'yml') {
                $fileName .= '.yml';
            }

            file_put_contents($fileName, $yaml);
        } else {
            echo $yaml, "\n";
        }
    }

    protected function getDefaultConfigArray()
    {
        return [
            'binaries' => [
                'git'       => '/usr/bin/git',
                'rsync'     => '/usr/bin/rsync',
                'setfacl'   => '/usr/bin/setfacl',
                'mysqldump' => '/usr/bin/mysqldump',
                'pg_dump'   => '/usr/bin/pg_dump',
            ],
            'permissions' => [
                'user'  => 'www-data',
                'group' => 'www-data',
            ],
            'acls' => [
                'users' => [
                    'www-data',
                    get_current_user(),
                ],
                'groups' => [
                    'www-data',
                    get_current_user(),
                ],
            ],
            'sites' => [
                'example.com' => [
                    'paths' => [
                        'site'   => '/var/www/sites/example.com',
                        'source' => '/data/deployment/source/example.com',
                        'backup' => '/data/deployment/backup/',
                    ],
                    'backup' => [
                        'timestamp' => true,
                        'files'     => [
                            'enabled' => true,
                            'exclude' => [
                                'vendor',
                                'public',
                            ],
                        ],
                        'database' => [
                            'enabled'   => false,
                            'auth_file' => '/var/www/sites/example.com/app/config/config.yml',
                        ],
                    ],
                ],
            ],
        ];
    }
}
