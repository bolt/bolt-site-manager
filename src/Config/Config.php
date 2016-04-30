<?php

namespace Bolt\Deploy\Config;

class Config
{
    /** @var array */
    protected $paths;
    /** @var Site[] */
    protected $sites;
    /** @var array */
    protected $permissions;
    /** @var array */
    protected $acls;

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
        $this->permissions = $config['permissions'];
        $this->acls = $config['acls'];
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

    /**
     * @param string $type
     *
     * @return string
     */
    public function getPermission($type)
    {
        return $this->permissions[$type];
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     *
     * @return Config
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getAcl($type)
    {
        return $this->acls[$type];
    }

    /**
     * @return array
     */
    public function getAcls()
    {
        return $this->acls;
    }

    /**
     * @param array $acls
     *
     * @return Config
     */
    public function setAcls($acls)
    {
        $this->acls = $acls;

        return $this;
    }
}
