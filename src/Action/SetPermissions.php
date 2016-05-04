<?php

namespace Bolt\Deploy\Action;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;

/**
 * Permission & ACL action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SetPermissions extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $output = new ConsoleOutput();
        $sitePath = $this->siteConfig->getPath('site');
        $hasUser = $this->userExists($output, $this->config->getPermission('user'));
        $hasGroup = $this->groupExists($output, $this->config->getPermission('group'));
        if ($hasUser && $hasGroup) {
            $chown = sprintf(
                'sudo chown -R %s:%s %s',
                $this->config->getPermission('user'),
                $this->config->getPermission('group'),
                $sitePath
            );
            $this->runProcess(new Process($chown));
        }

        $setfacls = [];
        foreach ($this->config->getAcl('users') as $user) {
            if ($this->userExists($output, $user)) {
                $setfacls[] = sprintf('-m u:%s:rwX', $user);
            }
        }
        foreach ($this->config->getAcl('groups') as $group) {
            if ($this->groupExists($output, $group)) {
                $setfacls[] = sprintf('-m g:%s:rwX', $group);
            }
        }

        $setfacl = sprintf('%s %s', implode(' ', $setfacls), $sitePath);
        $this->runProcess(new Process('sudo setfacl -R ' . $setfacl));
        $this->runProcess(new Process('sudo setfacl -dR ' . $setfacl));

        if ($this->logFile !== null) {
            throw new \RuntimeException(sprintf('Failed to set permissions, details logged to %s', $this->logFile));
        }
    }
}
