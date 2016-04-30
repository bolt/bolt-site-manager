<?php

namespace Bolt\Deploy\Config;

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
    /** @var boolean */
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
        return $this->paths[$path];
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
    public function isBackup()
    {
        return $this->backup;
    }

    /**
     * @param boolean $backup
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
