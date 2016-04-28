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
    /** @var string */
    protected $path;
    /** @var boolean */
    protected $backup;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $siteConfig
     */
    public function __construct($name, $siteConfig)
    {
        $this->name = $name;
        $this->path = $siteConfig['path'];
        $this->backup = $siteConfig['backup'];
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
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return Site
     */
    public function setPath($path)
    {
        $this->path = $path;

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
}
