<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Zicht\Bundle\PageBundle\Entity\ContentItem;
use Zicht\Bundle\PageBundle\Entity\Page;

class CheckContentItemsCommand extends Command
{
    /** @var bool */
    protected $isVeryVerbose;

    /** @var bool */
    protected $force;

    /** @var ConsoleLogger */
    protected $logger;

    /** @var ManagerRegistry */
    private $doctrine;

    protected static $defaultName = 'zicht:page:contentitems:check';

    public function __construct(ManagerRegistry $doctrine, string $name = null)
    {
        parent::__construct($name);
        $this->doctrine = $doctrine;
    }

    protected function configure()
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Set this flag te remove/invalid broken content items')
            ->setHelp('Check/validate the page content items');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->force = $input->getOption('force');
        $this->logger = $this->getLogger($output);
        $this->isVeryVerbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->doctrine->getManager();

        foreach ($this->getBasePageMeta() as $meta) {
            $this->logger->info(sprintf('Checking all sub classes of "%s"', $meta->getName()));

            foreach ($meta->subClasses as $class) {
                $entities = $em->getRepository($class)->findAll();

                /** @var Page $entity */
                foreach ($entities as $entity) {
                    $debugString = sprintf(
                        '[%04d] [%s] %s',
                        $entity->getId(),
                        $this->getShortName($class, $meta->getName()),
                        $entity->getTitle()
                    );
                    $this->logger->debug($debugString);
                    $matrix = $entity->getContentItemMatrix();

                    /** @var ContentItem $contentitem */
                    foreach ($entity->getContentItems() as $contentitem) {
                        $failed = false;

                        if (in_array($contentitem->getRegion(), $matrix->getRegions())) {
                            if (!in_array(get_class($contentitem), $matrix->getTypes($contentitem->getRegion()))) {
                                $warningString = sprintf(
                                    '[%04d] [%s] [%s] "%s" not allowed for defined types "%s"',
                                    $entity->getId(),
                                    $this->getShortName($class, $meta->getName()),
                                    $contentitem->getRegion(),
                                    $this->getShortName(get_class($contentitem), $matrix->getNamespacePrefix()),
                                    implode(
                                        '", "',
                                        array_map(
                                            function ($name) use ($matrix) {
                                                return $this->getShortName($name, $matrix->getNamespacePrefix());
                                            },
                                            $matrix->getTypes($contentitem->getRegion())
                                        )
                                    )
                                );

                                $this->logger->warning($warningString);
                                $failed = true;
                            }
                        } else {
                            $this->logger->warning(
                                sprintf(
                                    '[%04d] [%s] Region "%s" not allowed for defined regions "%s"',
                                    $entity->getId(),
                                    $this->getShortName($class, $meta->getName()),
                                    $contentitem->getRegion(),
                                    implode('", "', $matrix->getRegions())
                                )
                            );
                            $failed = true;
                        }

                        if ($failed && $this->force) {
                            $entity->removeContentItem($contentitem);
                            $em->flush();
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $class
     * @param string $baseClass
     * @return mixed
     */
    protected function getShortName($class, $baseClass)
    {
        preg_match(sprintf('#%s\\\?(?P<name>[^$]+)#', preg_quote($baseClass, '#')), $class, $m);

        return (isset($m['name']) && !$this->isVeryVerbose) ? $m['name'] : $class;
    }

    /**
     * @return ConsoleLogger
     */
    protected function getLogger(OutputInterface $output)
    {
        return new ConsoleLogger(
            $output,
            [
                LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
                LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
                LogLevel::DEBUG => OutputInterface::VERBOSITY_VERBOSE,
            ],
            [
                LogLevel::WARNING => 'comment',
                LogLevel::DEBUG => 'fg=cyan',
            ]
        );
    }

    /**
     * @return \Generator|\Doctrine\ORM\Mapping\ClassMetadata[]
     */
    protected function getBasePageMeta()
    {
        $allMeta = $this->doctrine->getManager()->getMetadataFactory()->getAllMetadata();
        $done = [];

        /** @var \Doctrine\ORM\Mapping\ClassMetadata $meta */
        foreach ($allMeta as $meta) {
            if (!empty($meta->subClasses) && is_a($meta->getName(), Page::class, true)) {
                foreach ($meta->parentClasses as $parent) {
                    if (in_array($parent, $done)) {
                        continue 2;
                    }
                }
                if (in_array($meta->getName(), $done)) {
                    continue;
                }
                $done[] = $meta->getName();
                yield $meta;
            }
        }
    }
}
