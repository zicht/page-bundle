<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Command;

use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
            ->addArgument('entity', InputArgument::OPTIONAL, 'Only do a specific entity')
            ->addOption('where', 'w', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Add a WHERE query')
        ;
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $aliaser = $this->getContainer()->get('zicht_page.page_aliaser');

        $onDone = $aliaser->setIsBatch(true);

        if ($entity = $input->getArgument('entity')) {
            $repo = $this->getContainer()->get('doctrine')->getRepository($entity);
        } else {
            $repo = $this->getContainer()->get('zicht_page.page_manager')->getBaseRepository();
        }
        $q = $repo->createQueryBuilder('p');

        foreach ($input->getOption('where') as $filter) {
            $q->andWhere($filter);
        }

        if ($output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln("Querying records ...");
        }
        $items = $q->getQuery()->execute();

        $progress = null;
        $progress = new ProgressBar($output, count($items));
        if ($output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE) {
            $progress->display();
        }
        foreach ($items as $page) {
            $progress->advance(1);
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln("Aliasing page \"{$page}\"");
            }
            $result = $aliaser->createAlias($page);
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(" -> " . ($result ? '[created]' : '[already aliased]'));
            }
        }
        $progress->finish();
        call_user_func($onDone);
    }
}