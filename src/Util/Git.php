<?php

namespace Bolt\Deploy\Util;

use SebastianBergmann\Git\Git as GitBase;

/**
 * Git helper.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Git extends GitBase
{
    /**
     * Pull a branch from a remote.
     *
     * @param string $remote
     * @param string $branch
     */
    public function pull($remote = 'origin', $branch = 'master')
    {
        $this->execute(sprintf('pull %s %s', $remote, $branch));
    }
}
