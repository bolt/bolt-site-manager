<?php

namespace Bolt\Deploy\Action;

use AFM\Rsync\Rsync;
use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\Site;
use Bolt\Deploy\Console\Application;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Backup action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BackupFiles extends AbstractAction
{
    /** @var string */
    protected $backupPath;

    /**
     * Constructor.
     *
     * @param Site $siteConfig
     */
    public function __construct($siteName, Config $config, OutputInterface $output)
    {
        parent::__construct($siteName, $config, $output);

        if ($this->siteConfig->getPath('backup') === '/') {
            throw new RuntimeException('Stubbornly refusing to use / as a backup target.');
        }

        $siteConfig = $this->siteConfig;
        $this->backupPath = $siteConfig->isBackupTimestamp()
            ? sprintf('%s%s/%s/files/', $siteConfig->getPath('backup'), $siteConfig->getName(), Application::$timestamp)
            : sprintf('%s%s/files/', $siteConfig->getPath('backup'), $siteConfig->getName())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->siteConfig->isBackupFiles()) {
            return;
        }

        $fs = new Filesystem();
        if (!$fs->exists($this->backupPath)) {
            $fs->mkdir($this->backupPath);
        }

        $rsync = new Rsync();
        $rsync->setArchive(true);
        $rsync->setExclude($this->siteConfig->getBackupExcludeFiles());
        $rsync->setFollowSymLinks(true);

        $command = $rsync->getCommand($this->siteConfig->getPath('site'), $this->backupPath);
        $this->runProcess(new Process('sudo ' . $command));

        if ($this->logFile !== null) {
            throw new \RuntimeException(sprintf('Failed to backup files, details logged to %s', $this->logFile));
        }
    }

    /**
     * @return string
     */
    public function getBackupPath()
    {
        return $this->backupPath;
    }
}
