<?php

namespace Bolt\Deploy\Config;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Filesystem\Filesystem;

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
     * @param string|array $configFileParameter
     */
    public function __construct($configFileParameter = null)
    {
        $this->initialise($configFileParameter);
    }

    /**
     * Initialise config parameters.
     *
     * @param string|null $configFileParameter
     */
    public function initialise($configFileParameter = null)
    {
        $config = [];
        $configFileGlobal = '/etc/bolt-site-manager.yml';
        $configFileHome = getenv('HOME') . '/.bolt-site-manager.yml';
        $configFileLocal = getcwd() . '/.bolt-site-manager.yml';

        $fs = new Filesystem();
        if ($fs->exists($configFileGlobal)) {
            $merge = $this->loadConfiguration($configFileGlobal);
            $config = static::mergeRecursiveDistinct($config, $merge);
        }
        if ($fs->exists($configFileHome)) {
            $merge = $this->loadConfiguration($configFileHome);
            $config = static::mergeRecursiveDistinct($config, $merge);
        }
        if ($fs->exists($configFileLocal)) {
            $merge = $this->loadConfiguration($configFileLocal);
            $config = static::mergeRecursiveDistinct($config, $merge);
        }
        if ($configFileParameter === null && $fs->exists($configFileParameter)) {
            $merge = $this->loadConfiguration($configFileParameter);
            $config = static::mergeRecursiveDistinct($config, $merge);
        }
        $config = $this->processConfiguration($config);

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
        $groupName = posix_getgrgid(posix_geteuid())['name'];

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
                'groups' => [
                    $groupName,
                ],
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
                            'vendor',
                        ],
                    ],
                    'database' => [
                        'enabled'   => false,
                        'auth_file' => null,
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
    public static function mergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                if (static::isIndexedArray($value)) {
                    $merged[$key] = array_merge($merged[$key], $value);
                } else {
                    $merged[$key] = static::mergeRecursiveDistinct($merged[$key], $value);
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @param array $arr
     *
     * @return bool
     */
    public static function isIndexedArray(array $arr)
    {
        foreach ($arr as $key => $val) {
            if ($key !== (int) $key) {
                return false;
            }
        }

        return true;
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

        return [
            'root' => $delegatingLoader->load($configFile),
        ];
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function processConfiguration(array $config)
    {
        $configuration = new ConfigurationTree();
        $processor = new Processor();

        return $processor->processConfiguration(
            $configuration,
            $config
        );
    }
}
