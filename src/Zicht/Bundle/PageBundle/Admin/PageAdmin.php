<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Admin;

use Symfony\Component\ClassLoader\ClassMapGenerator;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\MenuBundle\Manager\MenuManager;

/**
 * Admin for the messages catalogue
 */
class PageAdmin extends Admin
{
    protected $contentItemAdminCode;

    public function __construct($code, $class, $baseControllerName, $contentItemAdminCode)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->contentItemAdminCode = $contentItemAdminCode;
    }


    /**
     * @var PageManager
     */
    protected $pageManager;

    protected $dataGridValues = array(
        '_sort_by'      => 'date_updated',
        '_sort_order'   => 'DESC',
    );

    /**
     * @var MenuManager
     */
    protected $menuManager;

    public function setPageManager(PageManager $pageManager)
    {
        $this->pageManager = $pageManager;
    }


    public function setMenuManager(MenuManager $manager)
    {
        $this->menuManager = $manager;
    }

    protected $persistFilters = true;
    protected $templates = array();


    public function configureShowFields(ShowMapper $showMapper)
    {
        return $showMapper
            ->add('title')
        ;
    }


    public function configureListFields(ListMapper $listMapper)
    {
        return $listMapper
            ->addIdentifier('title')
            ->add('displayType')
            ->add('date_updated')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'view' => array(),
                    'edit' => array(),
                    'delete' => array()
                )
            ))
        ;
    }


    public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = false)
    {
        $admin = $this->configurationPool->getAdminByClass(get_class($object));
        if ($admin && get_class($admin) !== get_class($this)) {
            return $admin->generateObjectUrl($name, $object, $parameters, $absolute);
        }
        return parent::generateObjectUrl($name, $object, $parameters, $absolute);
    }


    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('title', null, array('required' => true))
            ->end()
        ;

        if ($this->getSubject()->getId()) {
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
                ->with('Menu', array('collapsible' => true, 'collapsed' => true))
                    ->add(
                        'menu_item',
                        'zicht_menu_item'
                    )
                ->end();
        }
    }


    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('title')
            ->add('id')
        ;
    }


    public function preUpdate($object)
    {
        $this->fixOneToMany($object);
    }

    public function prePersist($object)
    {
        $this->fixOneToMany($object);
    }


    protected function fixOneToMany(PageInterface $object)
    {
        $items = $object->getContentItems();
        if ($items) {
            foreach ($object->getContentItems() as $item) {
                $item->setPage($object);
            }
        }
        $this->menuManager->flush();
    }
}