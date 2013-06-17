<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Command;

use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;

/**
 * Generates URL aliases for all pages.
 */
class AliasCommand extends ContainerAwareCommand
{
    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setDescription("Creates aliases for all pages")
            ->setName('zicht:page:alias')
        ;
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $aliaser = $this->getContainer()->get('zicht_page.page_aliaser');

        foreach ($this->getContainer()->get('zicht_page.page_manager')->getBaseRepository()->findAll() as $page) {
            $aliaser->createAlias($page);
        }
    }
}