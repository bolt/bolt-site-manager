<?php

namespace Bolt\Deploy\Action;

use AFM\Rsync\Rsync;
use Bolt\Deploy\Config\Site;
use Bolt\Deploy\Console\Application;

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
        $this->enabled = $siteConfig->isBackupFiles();
        $this->sitePath = $siteConfig->getPath('site');
        $this->backupPath = $siteConfig->isBackupFilesTimestamp()
            ? sprintf('%s%s/', $siteConfig->getPath('backup'), Application::$timestamp)
            : $siteConfig->getPath('backup')
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
