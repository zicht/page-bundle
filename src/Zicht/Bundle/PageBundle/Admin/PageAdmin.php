<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Admin;

use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Zicht\Bundle\AdminBundle\Util\AdminUtil;
use Zicht\Bundle\MenuBundle\Entity\MenuItem;
use Zicht\Bundle\MenuBundle\Form\Subscriber\MenuItemPersistenceSubscriber;
use Zicht\Bundle\PageBundle\Entity\ContentItem;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Bundle\MenuBundle\Manager\MenuManager;
use Zicht\Bundle\UrlBundle\Aliasing\ProviderDecorator;
use Zicht\Bundle\UrlBundle\Url\Provider;
use Zicht\Util\Str;

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
     * @param Provider $urlProvider
     * @return void
     */
    public function setUrlProvider(Provider $urlProvider)
    {
        $this->urlProvider = $urlProvider;
    }

    /**
     * @{inheritDoc}
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        return $showMapper->add('title');
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
            );
    }

    /**
     * {@inheritDoc}
     */
    public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = false)
    {
        $admin = null;

        $adminClasses = $this->filterAdminClasses($this->getClassParents($object));

        foreach ($adminClasses as $class => $admins) {
            foreach ($admins as $code) {
                $admin = $this->configurationPool->getAdminByAdminCode($code);
                if (get_class($admin) !== PageAdmin::class) {
                    break;
                }
            }
        }

        if ($admin) {
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
            ->tab('admin.tab.general')
            ->add('title', null, array('required' => true))
            ->end()->end() //needed to do twice, since a tab is a group surrounding a 'with'
        ;

        if (($subject = $this->getSubject()) && $subject->getId()) {
            if ($subject->getContentItemMatrix() && $subject->getContentItemMatrix()->getTypes()) {
                if (!property_exists($subject, 'contentItems')) {
                    throw new \RuntimeException(sprintf('The zicht/page-bundle assumes that there is a property "contentItems" for the entity %s.  Note that this property must be either public of protected.', get_class($subject)));
                }

                $formMapper
                    ->tab('admin.tab.content')
                    ->add(
                        'contentItems',
                        'sonata_type_collection',
                        array(
                            'btn_add' => 'content_item.add'
                        ),
                        array(
                            'edit'   => 'inline',
                            'inline' => 'table',
                            'sortable' => 'weight',
                            'admin_code' => $this->code . '|' . $this->contentItemAdminCode
                        )
                    )
                    ->end()->end() //needed to do twice, since a tab is a group surrounding a 'with'
                ;

                $formMapper->getFormBuilder()->addEventListener(
                    FormEvents::SUBMIT,
                    function (FormEvent $e) {
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
                ->tab('admin.tab.menu')
                ->add('menu_item', 'zicht_menu_item', array('translation_domain' => $this->getTranslationDomain()))
                ->end()
                ->end();

            $formMapper
                ->getFormBuilder()
                ->addEventSubscriber(
                    new MenuItemPersistenceSubscriber(
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
        $filter->add('title')
            ->add('id');
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

    /**
     * Pre remove function
     *
     * @param mixed $object
     */
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

    /**
     * Reorder tabs
     *
     * @param FormMapper $formMapper
     * @param array $tabOrder
     *
     * @deprecated See Zicht\Bundle\AdminBundle\Util\AdminUtil::reorderTabs
     *
     */
    public function reorderTabs(FormMapper $formMapper, array $tabOrder)
    {
        AdminUtil::reorderTabs($formMapper, $tabOrder);
    }

    /**
     * Removes field (and also removes the tab when the tab/group is empty)
     *
     * @param string|array $fieldNames one fieldname or array of fieldnames
     * @param FormMapper $formMapper
     */
    public function removeFields($fieldNames, FormMapper $formMapper)
    {
        if (!is_array($fieldNames)) {
            $fieldNames = array($fieldNames);
        }

        foreach ($fieldNames as $fieldName) {
            $formMapper->remove($fieldName);
        }

        $this->removeEmptyGroups();

        return $this;
    }

    /**
     * Removes tab and all it's fields in it
     *
     * @param string $tabName
     * @param FormMapper $formMapper
     */
    public function removeTab($tabName, FormMapper $formMapper)
    {
        $tabs = $this->getFormTabs();

        if (array_key_exists($tabName, $tabs)) {
            $groups = $this->getFormGroups();

            foreach ($tabs[$tabName]['groups'] as $group) {
                if (isset($groups[$group])) {
                    foreach ($groups[$group]['fields'] as $field) {
                        $formMapper->remove($field);
                    }
                }
                unset($groups[$group]);
            }

            $this->setFormGroups($groups);
            $this->removeEmptyGroups();
        }
    }

    /**
     * Removes the empty tabs from the groups
     */
    public function removeEmptyGroups()
    {
        $tabs = $this->getFormTabs();
        $groups = $this->getFormGroups();

        foreach ($tabs as $tabKey => $tab) {
            foreach ($tab['groups'] as $tabGroup) {
                if (!array_key_exists($tabGroup, $groups)) {
                    unset($tabs[$tabKey]);
                }
            }
        }

        $this->setFormTabs($tabs);
    }

    /**
     * @{inheritDoc}
     */
    public function getLabel()
    {
        return sprintf('admin.label.%s', Str::infix(lcfirst(Str::classname(get_class($this))), '_'));
    }

    /**
     * Return array of parent classes
     *
     * @param $object
     *
     * @return array
     */
    private function getClassParents($object)
    {
        $class = new \ReflectionClass($object);
        $parents = [];
        while ($parent = $class->getParentClass()) {
            $parents[] = $parent->getName();
            $class = $parent;
        }

        return $parents;
    }

    /**
     * Filter admin classes that are not applicable for the given set of parent classes.
     *
     * @param array $parentClasses Array with just the class names that belong to an object. Usually the result of getClassParents.
     * @param bool $sortByParentClasses After filtering the index has changed of the admin classes, use this to resort by given parentClasses.
     *
     * @return array
     */
    private function filterAdminClasses($parentClasses, $sortByParentClasses = true)
    {
        $adminClasses = $this->configurationPool->getAdminClasses();
        $adminClasses = array_filter(
            $adminClasses,
            function ($key) use ($parentClasses) {
                return in_array($key, $parentClasses, true);
            },
            ARRAY_FILTER_USE_KEY
        );

        if ($sortByParentClasses) {
            $adminClassesSortedByParentClasses = [];

            foreach ($parentClasses as $idx => $value) {
                if (array_key_exists($value, $adminClasses)) {
                    $adminClassesSortedByParentClasses[$value] = $adminClasses[$value];
                }
            }

            $adminClasses = $adminClassesSortedByParentClasses;
        }

        return $adminClasses;
    }
}
