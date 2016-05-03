<?php

namespace Bolt\Deploy\Action;

use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\Site;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Permission & ACL action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SetPermissions extends AbstractAction
{
    /** @var string */
    protected $logFile;

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
                'chown -R %s:%s %s',
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
        $this->runProcess(new Process('setfacl -R ' . $setfacl));
        $this->runProcess(new Process('setfacl -dR ' . $setfacl));

        if ($this->logFile !== null) {
            throw new \RuntimeException(sprintf('Failed to set permissions, details logged to %s', $this->logFile));
        }
    }

    /**
     * Check if an operating system user exists.
     *
     * @param OutputInterface $output
     * @param string          $user
     *
     * @return bool
     */
    private function userExists(OutputInterface $output, $user)
    {
        exec(sprintf('id -u %s 2>&1 > /dev/null', $user), $cmdOutput, $cmdReturn);
        if ($cmdReturn !== 0) {
            $output->writeln(sprintf('<error>User name "%s" does not exist!</error>', $user));

            return false;
        }

        return true;
    }

    /**
     * Check if an operating system group exists.
     * 
     * @param OutputInterface $output
     * @param string          $group
     *
     * @return bool
     */
    private function groupExists(OutputInterface $output, $group)
    {
        exec(sprintf('id -g %s 2>&1 > /dev/null', $group), $cmdOutput, $cmdReturn);
        if ($cmdReturn !== 0) {
            $output->writeln(sprintf('<error>Group name "%s" does not exist!</error>', $group));

            return false;
        }

        return true;
    }

    /**
     * Run a process and log any failures.
     *
     * @param Process $process
     */
    private function runProcess(Process $process)
    {
        $process->run();
        if ($process->isSuccessful()) {
            return;
        }

        if ($this->logFile === null) {
            $this->logFile = tempnam(sys_get_temp_dir(), 'site-deploy-');
        }

        file_put_contents($this->logFile, $process->getErrorOutput(), FILE_APPEND);
    }
}
