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

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('zicht:page:test')
            ->addOption('base-url', '', InputOption::VALUE_REQUIRED, 'Base URL for testing urls', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pages = $this->getContainer()->get('zicht_page.page_manager')->getBaseRepository()->findAll();
        $urlProvider = $this->getContainer()->get('zicht_url.provider');

        $baseUrl = $input->getOption('base-url');

        if ($baseUrl) {
            $guzzle = new \Guzzle\Http\Client($baseUrl);
        } else {
            $guzzle = false;
        }
        foreach ($pages as $page) {
            if ($guzzle) {
                $output->write($baseUrl . $urlProvider->url($page));
                /** @var $req \Guzzle\Http\Message\Request */
                $req = $guzzle->get($urlProvider->url($page));
                $response = $req->send();
                if ($response->isError()) {
                    $output->writeln(': <error>' . (string) $response->getStatusCode() . '</error>');
                } else {
                    $output->writeln(': <info>' . (string) $response->getStatusCode() . '</info>');
                }
            }
        }
    }
}