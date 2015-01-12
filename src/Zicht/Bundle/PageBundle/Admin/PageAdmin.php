<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Admin;

use \Symfony\Component\ClassLoader\ClassMapGenerator;

use \Sonata\AdminBundle\Show\ShowMapper;
use \Sonata\AdminBundle\Admin\Admin;
use \Sonata\AdminBundle\Form\FormMapper;
use \Sonata\AdminBundle\Datagrid\DatagridMapper;
use \Sonata\AdminBundle\Datagrid\ListMapper;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use \Zicht\Bundle\MenuBundle\Entity\MenuItem;
use Zicht\Bundle\PageBundle\Entity\ContentItem;
use Zicht\Bundle\PageBundle\Entity\Page;
use \Zicht\Bundle\PageBundle\Manager\PageManager;
use \Zicht\Bundle\PageBundle\Model\PageInterface;

use \Zicht\Bundle\MenuBundle\Manager\MenuManager;
use \Zicht\Bundle\UrlBundle\Aliasing\ProviderDecorator;

/**
 * Admin for the messages catalogue
 */
class PageAdmin extends Admin
{
    /**
     * @var array
     */
    protected $dataGridValues = array(
        '_sort_by'      => 'date_updated',
        '_sort_order'   => 'DESC',
    );

    /**
     * @var bool
     */
    protected $persistFilters = true;

    /**
     * @var array
     */
    protected $templates = array();

    /**
     * @var PageManager
     */
    protected $pageManager;

    /**
     * @var MenuManager
     */
    protected $menuManager = null;

    /**
     * @var string
     */
    protected $contentItemAdminCode;

    /**
     * @var ProviderDecorator | null
     */
    private $urlProvider = null;

    /**
     * Constructor, overridden to be able to set the (required) content item admin code.
     *
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param string $contentItemAdminCode
     */
    public function __construct($code, $class, $baseControllerName, $contentItemAdminCode)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->contentItemAdminCode = $contentItemAdminCode;
    }

    /**
     * Set the page manager
     *
     * @param \Zicht\Bundle\PageBundle\Manager\PageManager $pageManager
     * @return void
     */
    public function setPageManager(PageManager $pageManager)
    {
        $this->pageManager = $pageManager;
    }


    /**
     * Set the menumanager, which is needed for flushing the menu items to the persistence layer whenever a page
     * is updated.
     *
     * @param \Zicht\Bundle\MenuBundle\Manager\MenuManager $manager
     * @return void
     */
    public function setMenuManager(MenuManager $manager)
    {
        $this->menuManager = $manager;
    }


    /**
     * Sets the url provider
     *
     * @param ProviderDecorator $urlProvider
     * @return void
     */
    public function setUrlProvider(ProviderDecorator $urlProvider)
    {
        $this->urlProvider = $urlProvider;
    }

    /**
     * @{inheritDoc}
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        return $showMapper
            ->add('title')
        ;
    }

    /**
     * @{inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        return $listMapper
            ->addIdentifier('title')
            ->add('displayType')
            ->add('date_updated')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                        'delete' => array()
                    )
                )
            )
        ;
    }


    /**
     * @{inheritDoc}
     */
    public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = false)
    {
        $admin = $this->configurationPool->getAdminByClass(get_class($object));
        if ($admin && get_class($admin) !== get_class($this)) {
            return $admin->generateObjectUrl($name, $object, $parameters, $absolute);
        }
        return parent::generateObjectUrl($name, $object, $parameters, $absolute);
    }


    /**
     * @{inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('title', null, array('required' => true))
            ->end()
        ;
        
        if ($this->getSubject()->getId()) {
            if ($this->getSubject()->getContentItemMatrix() && $this->getSubject()->getContentItemMatrix()->getTypes()) {
                $formMapper
                    ->with('Content')
                        ->add(
                            'contentItems',
                            'sonata_type_collection',
                            array(),
                            array(
                                'edit'   => 'inline',
                                'inline' => 'table',
                                'sortable' => 'weight',
                                'admin_code' => $this->code . '|' . $this->contentItemAdminCode
                            )
                        )
                    ->end()
                ;

                $formMapper->getFormBuilder()->addEventListener(
                    FormEvents::SUBMIT,
                    function(FormEvent $e) {
                        /** @var PageInterface $pageData */
                        $pageData = $e->getData();

                        $contentItems = $pageData->getContentItems();

                        foreach ($contentItems as $data) {

                            if (null === $data) {
                                continue;
                            }
                            $type = $data->getConvertToType();

                            if (!$data->getId() && $type !== get_class($data)) {
                                $item = new $type;

                                ContentItem::convert($data, $item);

                                $pageData->removeContentItem($data);
                                $pageData->addContentItem($item);
                            }
                        }
                        $e->setData($pageData);
                    },
                    64
                );
            }
            $formMapper
                ->with('Menu', array('collapsible' => true, 'collapsed' => true))
                    ->add(
                        'menu_item',
                        'zicht_menu_item',
                        array('translation_domain' => $this->getTranslationDomain())
                    )
                ->end()
                //add the subscriber (needed for Symfony >= 2.3)
                ->getFormBuilder()->addEventSubscriber(
                    new \Zicht\Bundle\MenuBundle\Form\Subscriber\MenuItemPersistenceSubscriber(
                        $this->menuManager,
                        $this->urlProvider,
                        'menu_item'
                    )
                );
        }
    }


    /**
     * @{inheritDoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('title')
            ->add('id')
        ;
    }

    /**
     * @{inheritDoc}
     */
    public function preUpdate($object)
    {
        $this->fixOneToMany($object);
    }

    /**
     * @{inheritDoc}
     */
    public function prePersist($object)
    {
        $this->fixOneToMany($object);
    }

    /**
     * Fixes the many-to-one side of the one-to-many content items and flushes the menu manager.
     *
     * @param \Zicht\Bundle\PageBundle\Model\PageInterface $object
     * @return void
     */
    protected function fixOneToMany(PageInterface $object)
    {
        $items = $object->getContentItems();
        if ($items) {
            foreach ($object->getContentItems() as $item) {
                $item->setPage($object);
            }
        }
        if ($this->menuManager) {
            $this->menuManager->flush();
        }
    }

    public function preRemove($object)
    {
        if (!is_null($this->urlProvider) && !is_null($this->menuManager)) {
            $url      = $this->urlProvider->url($object);
            $menuItem = $this->menuManager->getItem($url);

            if ($menuItem instanceof MenuItem) {
                $this->menuManager->removeItem($menuItem);
                $this->menuManager->flush();
            }
        }
    }
}