<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\CollectionType as SonataCollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Zicht\Bundle\MenuBundle\Entity\MenuItem;
use Zicht\Bundle\MenuBundle\Form\MenuItemType;
use Zicht\Bundle\MenuBundle\Form\Subscriber\MenuItemPersistenceSubscriber;
use Zicht\Bundle\MenuBundle\Manager\MenuManager;
use Zicht\Bundle\PageBundle\Entity\ContentItem;
use Zicht\Bundle\PageBundle\Entity\Page;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Bundle\UrlBundle\Url\Provider;
use Zicht\Util\Str;

/**
 * Admin for the messages catalogue
 *
 * @template T of Page
 * @extends AbstractAdmin<T>
 */
class PageAdmin extends AbstractAdmin
{
    /** @var bool */
    protected $persistFilters = true;

    /** @var array */
    protected $templates = [];

    /**
     * @var PageManager
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $pageManager;

    /** @var MenuManager */
    protected $menuManager = null;

    /** @var string */
    protected $contentItemAdminCode;

    /** @var Provider|null */
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

    public function setPageManager(PageManager $pageManager): void
    {
        $this->pageManager = $pageManager;
    }

    /**
     * Set the menumanager, which is needed for flushing the menu items to the persistence layer whenever a page
     * is updated.
     */
    public function setMenuManager(MenuManager $manager): void
    {
        $this->menuManager = $manager;
    }

    public function setUrlProvider(Provider $urlProvider): void
    {
        $this->urlProvider = $urlProvider;
    }

    public function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper->add('title');
    }

    public function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, ['route' => ['name' => 'edit']])
            ->add('displayType')
            ->add('date_updated')
            ->add(
                ListMapper::NAME_ACTIONS,
                ListMapper::TYPE_ACTIONS,
                [
                    'actions' => [
                        'view' => [],
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    public function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('admin.tab.general')
            ->add('title', null, ['required' => true])
            ->end()->end();

        if (($subject = $this->getSubject()) && $subject->getId()) {
            if ($subject->getContentItemMatrix() && $subject->getContentItemMatrix()->getTypes()) {
                if (!($subject instanceof ContentItemContainer)) {
                    throw new \RuntimeException(sprintf('The zicht/page-bundle assumes that entity %s implements the ContentItemContainer interface.', get_class($subject)));
                }

                $form
                    ->tab('admin.tab.content')
                    ->add(
                        'contentItems',
                        SonataCollectionType::class,
                        [
                            'btn_add' => 'content_item.add',
                        ],
                        [
                            'edit' => 'inline',
                            'inline' => 'table',
                            'sortable' => 'weight',
                            'admin_code' => $this->code . '|' . $this->contentItemAdminCode,
                        ]
                    )
                    ->end()->end();

                $form->getFormBuilder()->addEventListener(
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
                                $item = new $type();

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
            $form
                ->tab('admin.tab.menu')
                ->add('menu_item', MenuItemType::class, ['translation_domain' => $this->getTranslationDomain()])
                ->end()
                ->end();

            $form
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

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('title')
            ->add('id');
    }

    public function preUpdate(object $object): void
    {
        $this->fixOneToMany($object);
    }

    public function prePersist(object $object): void
    {
        $this->fixOneToMany($object);
    }

    /**
     * Fixes the many-to-one side of the one-to-many content items and flushes the menu manager.
     */
    protected function fixOneToMany(PageInterface $object): void
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

    public function preRemove(object $object): void
    {
        if (!is_null($this->urlProvider) && !is_null($this->menuManager)) {
            $url = $this->urlProvider->url($object);
            $menuItem = $this->menuManager->getItem($url);

            if ($menuItem instanceof MenuItem) {
                $this->menuManager->removeItem($menuItem);
                $this->menuManager->flush();
            }
        }
    }

    /**
     * Removes field (and also removes the tab when the tab/group is empty)
     *
     * @param string|array $fieldNames one fieldname or array of fieldnames
     * @return self
     */
    public function removeFields($fieldNames, FormMapper $formMapper)
    {
        if (!is_array($fieldNames)) {
            $fieldNames = [$fieldNames];
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
     */
    public function removeTab($tabName, FormMapper $formMapper)
    {
        $tabs = $this->getFormTabs();

        if (array_key_exists($tabName, $tabs)) {
            $groups = $this->getFormGroups();

            if (!is_array($groups)) {
                return;
            }

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

        if (!is_array($tabs)) {
            return;
        }

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

    public function configure(): void
    {
        $this->setLabel(sprintf('admin.label.%s', Str::infix(lcfirst(Str::classname(get_class($this))), '_')));
        $this->setTranslationDomain('admin');
    }
}
