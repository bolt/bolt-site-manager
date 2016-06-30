<?php

namespace Bolt\Deploy\Util;

use Phar;
use Seld\PharUtils\Timestamps;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Process;

/**
 * Compiler class to create bolt-site-manager.phar file(s).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Compiler
{
    /** @var string */
    private $rootDir;
    /** @var string */
    private $version;
    /** @var string */
    private $branchAliasVersion = '';
    /** @var \DateTime */
    private $versionDate;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->rootDir = dirname(dirname(__DIR__));
    }

    /**
     * Compiles bolt-site-manager command into a PHAR file
     *
     * @param string $pharFile The full path to the file to create
     *
     * @throws \RuntimeException
     */
    public function compile($pharFile = 'bolt-site-manager.phar')
    {
        $fs = new Filesystem();
        if ($fs->exists($pharFile)) {
            $fs->remove($pharFile);
        }

        $process = new Process('git log --pretty="%H" -n1 HEAD', $this->rootDir);
        if ($process->run() != 0) {
            throw new \RuntimeException(
                'Can\'t run git log. You must ensure that compile is run from a bolt-site-manager git repository clone and that git binary is available.'
            );
        }
        $this->version = trim($process->getOutput());

        $process = new Process('git log -n1 --pretty=%ci HEAD', $this->rootDir);
        if ($process->run() != 0) {
            throw new \RuntimeException(
                'Can\'t run git log. You must ensure that compile is run from a bolt-site-manager git repository clone and that git binary is available.'
            );
        }
        $this->versionDate = new \DateTime(trim($process->getOutput()));
        $this->versionDate->setTimezone(new \DateTimeZone('UTC'));

        $process = new Process('git describe --tags --exact-match HEAD');
        try {
            $this->version = trim($process->getOutput());
        } catch (LogicException $e) {
            echo 'No git tags found on repository. Skipping.', "\n";
        }

        $phar = new Phar($pharFile, 0, 'bolt-site-manager.phar');
        $phar->setSignatureAlgorithm(Phar::SHA1);

        // Start buffering Phar write operations, do not modify the Phar object on disk
        $phar->startBuffering();

        $finderSort = function (SplFileInfo $a, SplFileInfo $b) {
            return strcmp(strtr($a->getRealPath(), '\\', '/'), strtr($b->getRealPath(), '\\', '/'));
        };

        // Compile in package files
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->in($this->rootDir . '/src/')
            ->sort($finderSort)
        ;
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        // Compile in Symfony files
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('composer-schema.json')
            ->name('LICENSE')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->in([
                $this->rootDir . '/vendor/albertofem/rsync-lib/src/',
                $this->rootDir . '/vendor/composer/ca-bundle/src/',
                $this->rootDir . '/vendor/composer/composer/',
                $this->rootDir . '/vendor/composer/semver/src/',
                $this->rootDir . '/vendor/nesbot/carbon/src/',
                $this->rootDir . '/vendor/justinrainbow/json-schema/src/',
                $this->rootDir . '/vendor/psr/log/',
                $this->rootDir . '/vendor/seld/jsonlint/src/',
                $this->rootDir . '/vendor/symfony/',
            ])
            ->sort($finderSort)
        ;
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        // Compile in Composer autoload files
        $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/autoload.php'));
        $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/composer/autoload_namespaces.php'));
        $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/composer/autoload_psr4.php'));
        $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/composer/autoload_classmap.php'));
        $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/composer/autoload_files.php'));
        $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/composer/autoload_real.php'));
        if (file_exists($this->rootDir . '/vendor/composer/autoload_static.php')) {
            $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/composer/autoload_static.php'));
        }
        if (file_exists($this->rootDir . '/vendor/composer/include_paths.php')) {
            $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/composer/include_paths.php'));
        }
        $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/composer/ClassLoader.php'));

        // Extras
        $this->addFile($phar, new SplFileInfo($this->rootDir . '/vendor/fabpot/php-cs-fixer/Symfony/CS/ToolInfo.php'));

        $this->addComparatorBin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        // Stop buffering write requests to the Phar archive, and save changes to disk
        $phar->stopBuffering();

        if (extension_loaded('zlib')) {
            $phar->compressFiles(Phar::GZ);
        }

        $this->addFile($phar, new SplFileInfo($this->rootDir . '/LICENSE'), false);

        unset($phar);

        // Re-sign the Phar with reproducible timestamp / signature
        $util = new Timestamps($pharFile);
        $util->updateTimestamps($this->versionDate);
        $util->save($pharFile, Phar::SHA1);
    }

    /**
     * Add a file to the Phar buffer.
     *
     * @param Phar        $phar
     * @param SplFileInfo $file
     * @param bool        $strip
     */
    private function addFile(Phar $phar, SplFileInfo $file, $strip = true)
    {
        $path = strtr(str_replace($this->rootDir . DIRECTORY_SEPARATOR, '', $file->getRealPath()), '\\', '/');

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n" . $content . "\n";
        }

        if ($path === 'src/Comparator.php') {
            $content = str_replace('@package_version@', $this->version, $content);
            $content = str_replace('@package_branch_alias_version@', $this->branchAliasVersion, $content);
            $content = str_replace('@release_date@', $this->versionDate->format('Y-m-d H:i:s'), $content);
        }

        $phar->addFromString($path, $content);
    }

    /**
     * Add the 'bin' file to the Phar buffer.
     *
     * @param Phar $phar
     */
    private function addComparatorBin(Phar $phar)
    {
        $content = file_get_contents($this->rootDir . '/bin/bolt-site-manager');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/bolt-site-manager', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        $stub = <<<'EOF'
#!/usr/bin/env php
<?php

/*
 * Copyright (c) 2015-2016 Gawain Lynch <gawain.lynch@gmail.com>
 */

Phar::mapPhar('bolt-site-manager.phar');

EOF;

        // add warning once the phar is older than 60 days
        if (preg_match('{^[a-f0-9]+$}', $this->version)) {
            $warningTime = (int) $this->versionDate->format('U') + 60 * 86400;
            $stub .= "define('COMPARTOR_DEV_WARNING_TIME', $warningTime);\n";
        }

        return $stub . <<<'EOF'
require 'phar://bolt-site-manager.phar/bin/bolt-site-manager';

__HALT_COMPILER();
EOF;
    }
}
