<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Manager;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zicht\Bundle\PageBundle\Entity\ViewablePageRepository;
use Zicht\Bundle\PageBundle\Event;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Util\Str;

/**
 * Main service for page management
 */
class PageManager
{
    /**
     * @var array
     */
    private $mappings = array();

    /**
     * @var null|PageInterface
     */
    private $loadedPage = null;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $pageClassName;

    /**
     * @var string
     */
    private $contentItemClassName;

    /**
     * Construct the page manager with the specified dependencies.
     *
     * @param Registry $doctrine
     * @param EventDispatcher $dispatcher
     * @param string $pageClassName
     * @param string $contentItemClassName
     */
    public function __construct(Registry $doctrine, $dispatcher, $pageClassName, $contentItemClassName)
    {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->eventDispatcher = $dispatcher;

        $this->pageClassName = $pageClassName;
        $this->contentItemClassName = $contentItemClassName;

        $this->mappings[$pageClassName] = array();
        $this->mappings[$contentItemClassName] = array();
    }

    /**
     * Returns the template of the page from the bundle the entity is part of.
     *
     * @param PageInterface $page
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getTemplate($page)
    {
        // determine page bundle name.
        $bundle = $this->getBundleName(ClassUtils::getRealClass(get_class($page)));
        return sprintf('%s:Page:%s.html.twig', $bundle, $page->getTemplateName());
    }

    /**
     * @param string $className
     * @return string
     * @throws \RuntimeException
     */
    protected function getBundleName($className)
    {
        $parts = explode('\\', $className);
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

            throw new \RuntimeException("Could not determine bundle name for " . $className);
        }
        $bundle = $vendor . $bundleName;
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

    /**
     * Returns the base repository, i.e. the repository of the page class.
     *
     * @return ObjectRepository
     */
    public function getBaseRepository()
    {
        return $this->em->getRepository($this->pageClassName);
    }

    /**
     * Sets the available page types
     *
     * @param array $pageTypes
     */
    public function setPageTypes($pageTypes)
    {
        $this->mappings[$this->pageClassName] = $pageTypes;
    }

    /**
     * @return array
     */
    public function getPageTypes()
    {
        return $this->mappings[$this->pageClassName];
    }

    /**
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }

    /**
     * Sets the available content item types.
     *
     * @param array $contentItemTypes
     */
    public function setContentItemTypes($contentItemTypes)
    {
        $this->mappings[$this->contentItemClassName] = $contentItemTypes;
    }

    /**
     * Adds the available page types and content item types to the class metadata's discriminatorMap
     *
     * @param ClassMetadata $c
     */
    public function decorateClassMetaData(ClassMetadata $c)
    {
        $parentClassName = $c->getName();

        if (isset($this->mappings[$parentClassName])) {
            $c->discriminatorMap = array();
            $c->discriminatorMap[strtolower(Str::classname($parentClassName))] = $parentClassName;
            foreach ($this->mappings[$parentClassName] as $className) {
                $name = Str::infix(Str::classname(Str::rstrip($className, Str::classname($parentClassName))), '-');
                $c->discriminatorMap[$name] = $className;
                $c->subClasses[] = $className;
            }
            $c->subClasses = array_unique($c->subClasses);
        }
    }

    /**
     * Find a page in the repository and trigger a view event.
     *
     * @param string $id
     * @return mixed
     *
     * @throws NotFoundHttpException
     */
    public function findForView($id)
    {
        $type = $this->doctrine->getConnection()->fetchColumn('SELECT type FROM page WHERE id=:id', array('id' => $id));
        if (!$type) {
            throw new NotFoundHttpException;
        }
        $types = $this->em->getClassMetadata($this->pageClassName)->discriminatorMap;

        $class = $types[$type];
        $repos = $this->em->getRepository($class);

        if ($repos instanceof ViewablePageRepository) {
            $ret = $repos->findForView($id);
        } else {
            $ret = $repos->find($id);
        }

        if (!$ret) {
            throw new NotFoundHttpException;
        }
        $this->setLoadedPage($ret);
        return $ret;
    }

    /**
     * Finds a page in the specified repository by the specified conditions.
     *
     * @param string $repository
     * @param array $conditions
     * @return null|object
     *
     * @throws NotFoundHttpException
     */
    public function findPageBy($repository, $conditions)
    {
        $ret = $this->em->getRepository($repository)->findOneBy($conditions);
        if (!$ret) {
            throw new NotFoundHttpException;
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
        $this->dispatch(Event\PageEvents::PAGE_VIEW, new Event\PageViewEvent($loadedPage));
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
                throw new NotFoundHttpException("There is no page currently loaded, but it was expected");
            }
        }
        return $this->loadedPage;
    }

    /**
     * Dispatch an event
     *
     * @param string $type
     * @param SymfonyEvent $event
     *
     * @return SymfonyEvent
     */
    public function dispatch($type, $event)
    {
        return $this->eventDispatcher->dispatch($type, $event);
    }
}
