<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Manager;

use \Doctrine\ORM\Mapping\ClassMetadata;
use \Doctrine\Bundle\DoctrineBundle\Registry;
use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \Zicht\Bundle\PageBundle\Event;
use \Zicht\Bundle\PageBundle\Model\PageInterface;
use \Zicht\Util\Str;

/**
 * Main service for page management
 */
class PageManager
{
    protected $mappings = array();
    protected $pageTypes = array();
    protected $loadedPage = null;

    /**
     * Construct the pagemanager with the specified dependencies.
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
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
     * @param \Zicht\Bundle\PageBundle\Model\PageInterface $page
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getTemplate($page)
    {
        // determine page bundle name.
        $className = get_class($page);
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
            throw new \RuntimeException("Could not determine bundle name for " . $className);
        }
        $bundle = $vendor . $bundleName;
        return sprintf('%s:Page:%s.html.twig', $bundle, $page->getTemplateName());
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
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getBaseRepository()
    {
        return $this->em->getRepository($this->pageClassName);
    }


    /**
     * Sets the available page types
     *
     * @param array $pageTypes
     * @return void
     */
    public function setPageTypes($pageTypes)
    {
        $this->mappings[$this->pageClassName] = $pageTypes;
    }


    /**
     * Sets the available content item types.
     *
     * @param array $contentItemTypes
     * @return void
     */
    public function setContentItemTypes($contentItemTypes)
    {
        $this->mappings[$this->contentItemClassName] = $contentItemTypes;
    }


    /**
     * Adds the available page types and content item types to the class metadata's discriminatorMap
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $c
     * @return void
     */
    public function decorateClassMetaData(ClassMetadata $c)
    {
        $parentClassName = $c->getName();

        if (isset($this->mappings[$parentClassName])) {
            $c->discriminatorMap = array();
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
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function findForView($id)
    {
        $type = $this->doctrine->getConnection()->fetchColumn('SELECT type FROM page WHERE id=:id', array('id' => $id));
        if (!$type) {
            throw new NotFoundHttpException;
        }
        $types = $this->em->getClassMetadata($this->pageClassName)->discriminatorMap;
        var_dump($types);
        $class = $types[$type];
        $ret = $this->em->getRepository($class)->find($id);
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
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
     * @param \Zicht\Bundle\PageBundle\Model\PageInterface $loadedPage
     * @return void
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
     * @return \Zicht\Bundle\PageBundle\Model\PageInterface
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getLoadedPage($default = null)
    {
        if (!$this->loadedPage) {
            if (is_callable($default)) {
                if ($page = call_user_func($default, $this)) {
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
     * @param \Symfony\Component\EventDispatcher\Event $event
     * @return mixed
     */
    public function dispatch($type, $event)
    {
        return $this->eventDispatcher->dispatch($type, $event);
    }
}