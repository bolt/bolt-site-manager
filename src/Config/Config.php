<?php

namespace Bolt\Deploy\Config;

class Config
{
    /** @var array */
    protected $binaries;
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
        $this->binaries = $config['binaries'];
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
    public function getBinary($name)
    {
        return $this->binaries[$name];
    }

    /**
     * @return array
     */
    public function getBinaries()
    {
        return $this->binaries;
    }

    /**
     * @param array $binaries
     *
     * @return Config
     */
    public function setBinaries($binaries)
    {
        $this->binaries = $binaries;

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
