<?php

namespace Bolt\Deploy\Config;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

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
     * @param string|array $configFile
     */
    public function __construct($configFile)
    {
        $config = $this->loadConfiguration($configFile);

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
        if ($type === 'homedirs') {
            if (empty($this->acls['homedirs'])) {
                $default = getenv('HOME') ? [dirname(getenv('HOME'))] : null;
                $default[] = '/home';

                return $this->acls['homedirs'] = array_unique($default);
            }

            return $this->acls['homedirs'];
        }

        return $this->acls[$type];
    }

    /**
     * @return array
     */
    public function getAcls()
    {
        $this->acls['homedirs'] = $this->getAcl('homedirs');

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

    /**
     * @return array
     */
    public function getDefaultConfig()
    {
        $userName = posix_getpwuid(posix_geteuid())['name'];

        return [
            'binaries' => [
                'git'       => '/usr/bin/git',
                'rsync'     => '/usr/bin/rsync',
                'setfacl'   => '/usr/bin/setfacl',
                'mysqldump' => '/usr/bin/mysqldump',
                'pg_dump'   => '/usr/bin/pg_dump',
            ],
            'permissions' => [
                'user'  => $userName,
                'group' => $userName,
            ],
            'acls' => [
                'users' => [
                    $userName,
                ],
                'homedirs' => null,
            ],
            'sites' => [
            ],
        ];
    }

    /**
     * @param string $siteName
     *
     * @return array
     */
    public function getDefaultSiteConfig($siteName)
    {
        return [
            $siteName => [
                'paths' => [
                    'site'   => null,
                    'source' => null,
                    'backup' => null,
                ],
                'backup' => [
                    'timestamp' => false,
                    'files'     => [
                        'enabled' => false,
                        'exclude' => [
                        ],
                        'database' => [
                            'enabled'   => false,
                            'auth_file' => null,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public function mergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            // if $key = 'accept_file_types, don't merge.
            if ($key == 'accept_file_types') {
                $merged[$key] = $array2[$key];
                continue;
            }

            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = static::mergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Load and validate configuration.
     *
     * @param $configFile
     *
     * @throws FileLoaderLoadException
     * @throws InvalidConfigurationException
     *
     * @return array
     */
    protected function loadConfiguration($configFile)
    {
        $configDirectory = (array) dirname($configFile);
        $locator = new FileLocator($configDirectory);
        $loaders = [
            new YamlFileLoader($locator),
        ];

        $loaderResolver = new LoaderResolver($loaders);
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        $configuration = new ConfigurationTree();
        $processor = new Processor();

        $config = [
            'root' => $delegatingLoader->load($configFile),
        ];

        return $processor->processConfiguration(
            $configuration,
            $config
        );
    }
}
