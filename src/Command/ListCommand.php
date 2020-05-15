<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\UrlBundle\Url\Provider;

/**
 * List all page urls. Useful for testing.
 */
class ListCommand extends Command
{
    protected static $defaultName = 'zicht:page:list';

    /** @var PageManager */
    private $pageManager;

    /** @var Provider */
    private $provider;

    public function __construct(PageManager $pageManager, Provider $provider, string $name = null)
    {
        parent::__construct($name);
        $this->pageManager = $pageManager;
        $this->provider = $provider;
    }

    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        $this->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Prepend a base url to the url\'s', null);
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pages = $this->pageManager->getBaseRepository()->findAll();
        $urlProvider = $this->provider;

        $baseUrl = rtrim($input->getOption('base-url'), '/');

        foreach ($pages as $page) {
            $output->writeln($baseUrl . $urlProvider->url($page));
        }
    }
}
