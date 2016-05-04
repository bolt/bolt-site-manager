<?php

namespace Bolt\Deploy\Action;

use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Console\Application;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Database backup action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BackupDatabase extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $fs = new Filesystem();
        $dir = dirname($this->getBackupFileName());
        if (!$fs->exists($dir)) {
            $fs->mkdir($dir);
        }

        $dbConfig = $this->siteConfig->getBackupDatabaseAuth();
        if ($dbConfig['driver'] === 'mysql') {
            $this->backupMySql($dbConfig);
        } elseif ($dbConfig['driver'] === 'postgres') {
            $this->backupPostres($dbConfig);
        } else {
            throw new RuntimeException(sprintf('Invalid database driver "%x" listed. Only "mysql" and "postgres" are supported.', $dbConfig['driver']));
        }
    }

    /**
     * @param array $dbConfig
     */
    protected function backupMySql(array $dbConfig)
    {
        $command = sprintf(
            '%s -u %s -p%s %s > %s',
            $this->config->getBinary('mysqldump'),
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['databasename'],
            $this->getBackupFileName()
        );
        $this->runCommand($command);

        if ($this->logFile !== null) {
            throw new \RuntimeException(sprintf('Failed to backup database, details logged to %s', $this->logFile));
        }
    }

    /**
     * @param array $dbConfig
     */
    protected function backupPostres(array $dbConfig)
    {
        $command = sprintf(
            'PGPASSWORD="%s" %s -U %s %s -f %s',
            $dbConfig['password'],
            $this->config->getBinary('pg_dump'),
            $dbConfig['username'],
            $dbConfig['databasename'],
            $this->getBackupFileName()
        );
        $this->runCommand($command);

        if ($this->logFile !== null) {
            throw new \RuntimeException(sprintf('Failed to backup database, details logged to %s', $this->logFile));
        }
    }

    /**
     * @param string $command
     *
     * @throws RuntimeException
     *
     * @return string
     */
    protected function runCommand($command)
    {
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * @return string
     */
    public function getBackupFileName()
    {
        if ($this->siteConfig->isBackupTimestamp()) {
            return sprintf(
                '%s%s/%s/database/%s.sql',
                $this->siteConfig->getPath('backup'),
                $this->siteConfig->getName(),
                Application::$timestamp,
                $this->siteConfig->getName()
            );
        }

        return sprintf(
            '%s%s/database/%s.sql',
            $this->siteConfig->getPath('backup'),
            $this->siteConfig->getName(),
            $this->siteConfig->getName()
        );
    }
}
