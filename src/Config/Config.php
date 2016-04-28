<?php

namespace Bolt\Deploy\Config;

class Config
{
    /** @var array */
    protected $paths;
    /** @var Site[] */
    protected $sites;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->paths = $config['paths'];
        foreach ($config['sites'] as $name => $data) {
            $this->sites[$name] = new Site($name, $data);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getPath($name)
    {
        return $this->paths[$name];
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
     * @return Config
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * @return Site
     */
    public function getSite($name)
    {
        if (!isset($this->sites[$name])) {
            return null;
        }

        return $this->sites[$name];
    }

    /**
     * @param Site $site
     *
     * @return Config
     */
    public function setSite($name, $site)
    {
        $this->sites[$name] = $site;

        return $this;
    }

    /**
     * @return Site[]
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param Site[] $sites
     *
     * @return Config
     */
    public function setSites($sites)
    {
        $this->sites = $sites;

        return $this;
    }
}
