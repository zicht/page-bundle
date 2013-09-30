<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Command;

use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;

class ListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('zicht:page:list')
            ->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Base URL for testing urls', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pages = $this->getContainer()->get('zicht_page.page_manager')->getBaseRepository()->findAll();
        $urlProvider = $this->getContainer()->get('zicht_url.provider');

        $baseUrl = rtrim($input->getOption('base-url'), '/');

        foreach ($pages as $page) {
            $output->writeln($baseUrl . $urlProvider->url($page));
        }
    }
}