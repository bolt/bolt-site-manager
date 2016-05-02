<?php

namespace Bolt\Deploy\Config;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * Individual site configuration.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Site
{
    /** @var string */
    protected $name;
    /** @var array */
    protected $paths;
    /** @var array */
    protected $backup;
    /** @var array */
    protected $exclude;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $siteConfig
     */
    public function __construct($name, $siteConfig)
    {
        $this->name = $name;
        $this->paths = $siteConfig['paths'];
        $this->backup = $siteConfig['backup'];
        $this->exclude = $siteConfig['exclude'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Site
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPath($path)
    {
        return rtrim($this->paths[$path], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param array $paths
     *
     * @return Site
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isBackupTimestamp()
    {
        return $this->backup['timestamp'];
    }

    /**
     * @return boolean
     */
    public function isBackupFiles()
    {
        return $this->backup['files']['enabled'];
    }

    /**
     * @return boolean
     */
    public function isBackupDatabase()
    {
        return $this->backup['database']['enabled'];
    }

    /**
     * @throws ParseException
     * @throws RuntimeException
     *
     * @return array
     */
    public function getBackupDatabaseAuth()
    {
        $parser = new Parser();
        $fs = new Filesystem();
        $fileName = $this->backup['database']['auth_file'];

        if (!$fs->exists($fileName)) {
            throw new RuntimeException(sprintf('Database credentials YAML file not found: %s', $fileName));
        }

        $config = $parser->parse(file_get_contents($fileName));

        if (!isset($config['database'])) {
            throw new RuntimeException(sprintf('Database credentials YAML file does not contain "database:" key:%s', $fileName));
        }
        if (!isset($config['database']['username'])) {
            throw new RuntimeException(sprintf('Database credentials YAML file does not contain database "username:" key:%s', $fileName));
        }
        if (!isset($config['database']['password'])) {
            throw new RuntimeException(sprintf('Database credentials YAML file does not contain database "password:" key:%s', $fileName));
        }

        return $config['database'];
    }

    /**
     * @param array $backup
     *
     * @return Site
     */
    public function setBackup($backup)
    {
        $this->backup = $backup;

        return $this;
    }

    /**
     * @return array
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @param array $exclude
     *
     * @return Site
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;

        return $this;
    }
}
