<?php

namespace Bolt\Deploy\Util;

use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\Site;
use DateTime;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Git helper.
 *
 * @author Sebastian Bergmann <sebastian@phpunit.de>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Git
{
    /** @var string */
    protected $gitBinary;
    /** @var string */
    protected $repositoryPath;

    /**
     * Constructor.
     *
     * @param Config $config
     * @param Site   $siteConfig
     */
    public function __construct(Config $config, Site $siteConfig)
    {
        $repositoryPath = $siteConfig->getPath('source');
        if (!is_dir($repositoryPath)) {
            throw new RuntimeException(
                sprintf(
                    'Directory "%s" does not exist',
                    $repositoryPath
                )
            );
        }

        $this->repositoryPath = realpath($repositoryPath);
        $this->gitBinary = $config->getBinary('git');
    }

    /**
     * Initialise a git repository.
     */
    public function init()
    {
        $this->execute('init');
    }

    /**
     * Add a file to the git tracker.
     *
     * @param string|array $target
     */
    public function add($target)
    {
        $this->execute(sprintf('add %s', implode(' ', (array) $target)));
    }

    /**
     * Commit file(s) to the git repository.
     *
     * @param string|array $target
     * @param string|null  $message
     */
    public function commit($target, $message = null)
    {
        if ($message === null) {
            $this->execute(sprintf('commit %s', implode(' ', (array) $target)));
        } else {
            $this->execute(sprintf('commit %s -m "%s"', implode(' ', (array) $target), $message));
        }
    }

    /**
     * Pull a branch from a remote.
     *
     * @param string $remote
     * @param string $branch
     */
    public function pull($remote = 'origin', $branch = 'master')
    {
        $this->execute(sprintf('pull --rebase %s %s', $remote, $branch));
    }

    /**
     * Perform an action on remote(s).
     *
     * @param string $action
     * @param array  $options
     */
    public function remote($action, $options = [])
    {
        $this->execute(sprintf('remote %s %s', $action, implode(' ', $options)));
    }

    /**
     * @param string $revision
     */
    public function checkout($revision)
    {
        $this->execute(sprintf('checkout --force --quiet %s', $revision));
    }

    /**
     * @return string
     */
    public function getCurrentBranch()
    {
        return $this->execute('symbolic-ref --short -q HEAD');
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return string
     */
    public function getDiff($from, $to)
    {
        return $this->execute(sprintf('diff --no-ext-diff %s %s', $from, $to));
    }

    /**
     * @return array
     */
    public function getRevisions()
    {
        $output = $this->execute('log --no-merges --date-order --reverse --format=medium');

        $numLines  = count($output);
        $revisions = [];

        for ($i = 0; $i < $numLines; $i++) {
            $tmp = explode(' ', $output[$i]);

            if ($tmp[0] == 'commit') {
                $sha1 = $tmp[1];
            } elseif ($tmp[0] == 'Author:') {
                $author = implode(' ', array_slice($tmp, 1));
            } elseif ($tmp[0] == 'Date:' && isset($author) && isset($sha1)) {
                $revisions[] = [
                    'author'  => $author,
                    'date'    => DateTime::createFromFormat(
                        'D M j H:i:s Y O',
                        implode(' ', array_slice($tmp, 3))
                    ),
                    'sha1'    => $sha1,
                    'message' => isset($output[$i + 2]) ? trim($output[$i + 2]) : '',
                ];

                unset($author);
                unset($sha1);
            }
        }

        return $revisions;
    }

    /**
     * Check if the current working copy has any uncommitted changes.
     *
     * @return bool
     */
    public function isWorkingCopyClean()
    {
        $output = $this->execute('status -s');

        return $output === '';
    }

    /**
     * @param string $command
     *
     * @throws RuntimeException
     *
     * @return string
     */
    protected function execute($command)
    {
        $command = sprintf('LC_ALL=en_US.UTF-8 %s -C %s %s', $this->gitBinary, escapeshellarg($this->repositoryPath), $command);
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }
}
