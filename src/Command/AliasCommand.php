<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\UrlBundle\Aliasing\Aliaser;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

/**
 * Generates URL aliases for all pages.
 */
class AliasCommand extends Command
{
    protected static $defaultName = 'zicht:page:alias';

    /** @var ManagerRegistry */
    private $doctrine;

    /** @var Aliaser */
    private $aliaser;

    /** @var PageManager */
    private $pageManager;

    public function __construct(ManagerRegistry $doctrine, Aliaser $aliaser, PageManager $pageManager, string $name = null)
    {
        parent::__construct($name);
        $this->doctrine = $doctrine;
        $this->aliaser = $aliaser;
        $this->pageManager = $pageManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates aliases for all pages')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Only do a specific entity')
            ->addOption('where', 'w', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Add a WHERE query')
            ->addOption('move', '', InputOption::VALUE_NONE, 'Force regeneration (use MOVE strategy for new aliases)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $aliaser = $this->aliaser;

        if ($input->getOption('move')) {
            $aliaser->setConflictingInternalUrlStrategy(Aliasing::STRATEGY_MOVE_PREVIOUS_TO_NEW);
        } else {
            $aliaser->setConflictingInternalUrlStrategy(Aliasing::STRATEGY_IGNORE);
        }

        $onDone = $aliaser->setIsBatch(true);

        if ($entity = $input->getArgument('entity')) {
            $repo = $this->doctrine->getRepository($entity);
        } else {
            $repo = $this->pageManager->getBaseRepository();
        }
        $q = $repo->createQueryBuilder('p');

        foreach ($input->getOption('where') as $filter) {
            $q->andWhere($filter);
        }

        if ($output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('Querying records ...');
        }
        $items = $q->getQuery()->execute();

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
                $output->writeln(' -> ' . ($result ? '[created]' : '[already aliased]'));
            }
        }
        $progress->finish();
        call_user_func($onDone);

        return 0;
    }
}
