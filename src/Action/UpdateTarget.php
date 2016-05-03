<?php

namespace Bolt\Deploy\Action;

use AFM\Rsync\Rsync;
use Bolt\Deploy\Config\Site;

/**
 * Update site action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class UpdateTarget extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $rsync = new Rsync();
        $rsync->setArchive(true);
        $rsync->setExclude(['.git']);

        $rsync->sync($this->siteConfig->getPath('source'), $this->siteConfig->getPath('site'));
    }
}
