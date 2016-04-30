<?php

namespace Bolt\Deploy\Action;

use AFM\Rsync\Rsync;
use Bolt\Deploy\Config\Site;
use Carbon\Carbon;

/**
 * Backup action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Backup implements ActionInterface
{
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
        $timestamp = Carbon::now()->format('Ymd-His');

        $this->sitePath = $siteConfig->getPath('site');
        $this->backupPath = sprintf('%s%s/', $siteConfig->getPath('backup'), $timestamp);
        $this->excluded = $siteConfig->getExclude();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
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
