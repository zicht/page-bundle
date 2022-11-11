<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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

    protected function configure()
    {
        $this->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Prepend a base url to the url\'s', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pages = $this->pageManager->getBaseRepository()->findAll();
        $urlProvider = $this->provider;

        $baseUrl = rtrim($input->getOption('base-url'), '/');

        foreach ($pages as $page) {
            $output->writeln($baseUrl . $urlProvider->url($page));
        }

        return 0;
    }
}
