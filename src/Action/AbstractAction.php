<?php

namespace Bolt\Deploy\Action;

use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\Site;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class for actions.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractAction implements ActionInterface
{
    /** @var Config */
    protected $config;
    /** @var Site */
    protected $siteConfig;
    /** @var OutputInterface */
    protected $output;

    /**
     * Constructor.
     *
     * @param string          $siteName
     * @param Config          $config
     * @param OutputInterface $output
     */
    public function __construct($siteName, Config $config, OutputInterface $output)
    {
        $this->config = $config;
        $this->siteConfig = $config->getSite($siteName);
        $this->output = $output;
    }
}
