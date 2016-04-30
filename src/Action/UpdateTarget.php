<?php

namespace Bolt\Deploy\Action;

use AFM\Rsync\Rsync;
use Bolt\Deploy\Config\Site;


/**
 * Update site action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class UpdateTarget implements ActionInterface
{
    /** @var string */
    protected $sourcePath;
    /** @var string */
    protected $sitePath;

    /**
     * Constructor.
     *
     * @param Site $siteConfig
     */
    public function __construct(Site $siteConfig)
    {
        $this->sourcePath = $siteConfig->getPath('source');
        $this->sitePath = $siteConfig->getPath('site');
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $rsync = new Rsync();
        $rsync->setArchive(true);
        $rsync->setExclude(['.git']);

        $rsync->sync($this->sourcePath, $this->sitePath);
    }
}
