<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zicht\Bundle\PageBundle\Entity\ViewablePageRepository;
use Zicht\Bundle\PageBundle\Event;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Util\Str;

class PageManager
{
    /** @var array */
    private $mappings = [];

    /** @var PageInterface|null */
    private $loadedPage = null;

    /** @var ManagerRegistry */
    private $doctrine;

    /** @var ObjectManager */
    private $em;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var string */
    private $pageClassName;

    /** @var string */
    private $contentItemClassName;

    public function __construct(ManagerRegistry $doctrine, EventDispatcherInterface $dispatcher, string $pageClassName, string $contentItemClassName)
    {
        $this->doctrine = $doctrine;
        $this->eventDispatcher = $dispatcher;

        $this->pageClassName = $pageClassName;
        $this->contentItemClassName = $contentItemClassName;

        $this->mappings[$pageClassName] = [];
        $this->mappings[$contentItemClassName] = [];
    }

    /**
     * Returns the template of the page from the bundle the entity is part of.
     *
     * @param PageInterface $page
     * @return string
     * @throws \RuntimeException
     */
    public function getTemplate($page)
    {
        $className = ClassUtils::getRealClass(get_class($page));
        if (strpos($className, 'App\\') === 0) {
            return sprintf('page/%s.html.twig', $page->getTemplateName());
        }

        // Not in App namespace, so determine the page bundle name.
        $bundle = $this->getTemplateBundleName($className);
        return sprintf('@%s/Page/%s.html.twig', $bundle, $page->getTemplateName());
    }

    /**
     * @param string $className
     * @return string
     * @throws \RuntimeException
     */
    protected function getBundleName($className)
    {
        $parts = explode('\\', $className);
        if (count($parts) > 1 && strpos($parts[0], 'App') === 0 && $parts[1] === 'Entity') {
            return $parts[0];
        }

        $vendor = array_shift($parts);
        $bundleName = null;
        foreach ($parts as $part) {
            if ($part === 'Entity') {
                break;
            }
            $bundleName = $part;
        }
        if (null === $bundleName) {
            $parentClass = get_parent_class($className);
            if ($parentClass) {
                return $this->getBundleName($parentClass);
            }

            throw new \RuntimeException('Could not determine bundle name for ' . $className);
        }

        $bundle = $vendor . $bundleName;

        return $bundle;
    }

    /**
     * @param class-string $className
     */
    protected function getTemplateBundleName(string $className): string
    {
        $bundle = $this->getBundleName($className);

        if (strlen($bundle) > 6 && substr($bundle, -6) === 'Bundle') {
            $bundle = substr($bundle, 0, -6);
        }

        return $bundle;
    }

    /**
     * Returns the page class
     *
     * @return string
     */
    public function getPageClass()
    {
        return $this->pageClassName;
    }

    public function getBaseRepository(): ObjectRepository
    {
        return $this->doctrine->getRepository($this->pageClassName);
    }

    /**
     * @param array $pageTypes
     */
    public function setPageTypes($pageTypes)
    {
        $this->mappings[$this->pageClassName] = $pageTypes;
    }

    public function getPageTypes(): array
    {
        return $this->mappings[$this->pageClassName];
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    /**
     * @param array $contentItemTypes
     */
    public function setContentItemTypes($contentItemTypes)
    {
        $this->mappings[$this->contentItemClassName] = $contentItemTypes;
    }

    /**
     * Adds the available page types and content item types to the class metadata's discriminatorMap
     */
    public function decorateClassMetaData(ClassMetadata $c)
    {
        $parentClassName = $c->getName();

        if (isset($this->mappings[$parentClassName])) {
            $c->discriminatorMap = [];
            $c->discriminatorMap[strtolower(Str::classname($parentClassName))] = $parentClassName;
            foreach ($this->mappings[$parentClassName] as $className) {
                $bundlePrefix = Str::infix($this->getBundleName($className), '-');
                $name = Str::infix(Str::classname(Str::rstrip($className, Str::classname($parentClassName))), '-');
                $combinedDiscriminator = sprintf('%s-%s', $bundlePrefix, $name);
                $c->discriminatorMap[$combinedDiscriminator] = $className;
                $c->subClasses[] = $className;
            }
            $c->subClasses = array_unique($c->subClasses);
        }
    }

    /**
     * Find a page in the repository and trigger a view event.
     *
     * @param string $id
     * @return PageInterface
     * @throws NotFoundHttpException
     */
    public function findForView($id)
    {
        /** @var Connection $conn */
        $conn = $this->doctrine->getConnection();
        $type = $conn->executeQuery('SELECT type FROM page WHERE id=:id', ['id' => $id])->fetchOne();
        if (!$type) {
            throw new NotFoundHttpException();
        }
        $types = $this->doctrine->getManagerForClass($this->pageClassName)
            ->getClassMetadata($this->pageClassName)->discriminatorMap;

        $class = $types[$type];
        /** @var ObjectRepository<PageInterface> $repository */
        $repository = $this->doctrine->getRepository($class);

        if ($repository instanceof ViewablePageRepository) {
            $page = $repository->findForView($id);
        } else {
            $page = $repository->find($id);
        }

        if (!$page) {
            throw new NotFoundHttpException();
        }

        $this->setLoadedPage($page);

        return $page;
    }

    /**
     * Finds a page in the specified repository by the specified conditions.
     *
     * @param string $repository
     * @param array $conditions
     * @return object|null
     * @throws NotFoundHttpException
     */
    public function findPageBy($repository, $conditions)
    {
        $ret = $this->doctrine->getRepository($repository)->findOneBy($conditions);
        if (!$ret) {
            throw new NotFoundHttpException();
        }
        return $ret;
    }

    /**
     * Returns all pages from the base repository
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getBaseRepository()->findAll();
    }

    /**
     * Set the loaded page, and trigger a view event
     *
     * @param PageInterface $loadedPage
     */
    public function setLoadedPage($loadedPage)
    {
        $this->dispatch(new Event\PageViewEvent($loadedPage), Event\PageEvents::PAGE_VIEW);
        $this->loadedPage = $loadedPage;
    }

    /**
     * Returns the currently loaded page, or default to the specified callback for loading it.
     *
     * @param callable $default
     * @return PageInterface
     * @throws NotFoundHttpException
     */
    public function getLoadedPage($default = null)
    {
        if (!$this->loadedPage) {
            if (is_callable($default)) {
                $page = call_user_func($default, $this);
                if ($page !== null) {
                    $this->setLoadedPage($page);
                }
            }

            if (!$this->loadedPage) {
                throw new NotFoundHttpException('There is no page currently loaded, but it was expected');
            }
        }
        return $this->loadedPage;
    }

    /**
     * @param SymfonyEvent $event
     * @param string $type
     * @return SymfonyEvent
     */
    public function dispatch($event, $type)
    {
        return $this->eventDispatcher->dispatch($event, $type);
    }
}
