<?php

namespace Bolt\Deploy\Action;

use AFM\Rsync\Rsync;
use Bolt\Deploy\Config\Site;
use Bolt\Deploy\Console\Application;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Backup action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BackupFiles implements ActionInterface
{
    /** @var boolean */
    protected $enabled;
    /** @var string */
    protected $sitePath;
    /** @var string */
    protected $backupPath;
    /** @var array */
    protected $excluded;

    /**
     * Constructor.
     *
     * @param Site $siteConfig
     */
    public function __construct(Site $siteConfig)
    {
        if ($siteConfig->getPath('backup') === '/') {
            throw new RuntimeException('Stubornly refusing to use / as a backup target.');
        }

        $this->enabled = $siteConfig->isBackupFiles();
        $this->sitePath = $siteConfig->getPath('site');
        $this->backupPath = $siteConfig->isBackupFilesTimestamp()
            ? sprintf('%s%s/%s/files/', $siteConfig->getPath('backup'), $siteConfig->getName(), Application::$timestamp)
            : sprintf('%s%s/files/', $siteConfig->getPath('backup'), $siteConfig->getName())
        ;
        $this->excluded = $siteConfig->getExclude();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->enabled) {
            return;
        }

        $fs = new Filesystem();
        if (!$fs->exists($this->backupPath)) {
            $fs->mkdir($this->backupPath);
        }

        $rsync = new Rsync();
        $rsync->setArchive(true);
        $rsync->setExclude($this->excluded);
        $rsync->setFollowSymLinks(true);

        $rsync->sync($this->sitePath, $this->backupPath);
    }

    /**
     * @return string
     */
    public function getBackupPath()
    {
        return $this->backupPath;
    }
}
