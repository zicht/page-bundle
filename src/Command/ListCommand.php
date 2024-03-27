<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\UrlBundle\Url\Provider as UrlProvider;

/**
 * List all page urls. Useful for testing.
 */
#[AsCommand('zicht:page:list')]
class ListCommand extends Command
{
    private PageManager $pageManager;

    private UrlProvider $urlProvider;

    public function __construct(PageManager $pageManager, UrlProvider $urlProvider, string $name = null)
    {
        parent::__construct($name);
        $this->pageManager = $pageManager;
        $this->urlProvider = $urlProvider;
    }

    protected function configure()
    {
        $this->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Prepend a base url to the url\'s', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pages = $this->pageManager->getBaseRepository()->findAll();
        $baseUrl = rtrim($input->getOption('base-url'), '/');

        foreach ($pages as $page) {
            $output->writeln($baseUrl . $this->urlProvider->url($page));
        }

        return Command::SUCCESS;
    }
}
