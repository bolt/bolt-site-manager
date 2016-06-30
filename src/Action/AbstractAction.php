<?php

namespace Bolt\Deploy\Action;

use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\Site;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Abstract class for actions.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractAction implements ActionInterface
{
    /** @var Config */
    protected $config;
    /** @var Site */
    protected $siteConfig;
    /** @var OutputInterface */
    protected $output;
    /** @var string */
    protected $logFile;

    /**
     * Constructor.
     *
     * @param string          $siteName
     * @param Config          $config
     * @param OutputInterface $output
     */
    public function __construct($siteName, Config $config, OutputInterface $output)
    {
        $this->config = $config;
        $this->siteConfig = $config->getSite($siteName);
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * Check if an operating system user exists.
     *
     * @param OutputInterface $output
     * @param string          $user
     *
     * @return bool
     */
    protected function userExists(OutputInterface $output, $user)
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
    protected function groupExists(OutputInterface $output, $group)
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
    protected function runProcess(Process $process)
    {
        $process->run();
        if ($process->isSuccessful()) {
            return;
        }

        if ($this->logFile === null) {
            $this->logFile = tempnam(sys_get_temp_dir(), 'bolt-site-manager-');
        }

        file_put_contents($this->logFile, $process->getErrorOutput(), FILE_APPEND);
    }
}
