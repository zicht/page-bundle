<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Admin;

use \Sonata\AdminBundle\Form\FormMapper;
use \Sonata\AdminBundle\Datagrid\ListMapper;
use \Sonata\AdminBundle\Admin\Admin;
use \Sonata\AdminBundle\Datagrid\DatagridMapper;

/**
 * Admin for content items.
 */
class ContentItemAdmin extends Admin
{
    /**
     * @{inheritDoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        if (!$this->isChild()) {
            $form->add('page')
            ;
        } else {
            $page = $this->getParentFieldDescription()->getAdmin()->getSubject();

            // This fixes a weird bug where the router does not get the correct parameters for the containing page.
            $this->getParentFieldDescription()->setOption('link_parameters', array('id' => $page->getId()));

            $form
                ->add('weight')
                ->add('internalName', 'text', array('disabled' => true, 'read_only' => true, 'required' => false))
                ->add('content_item_region', 'zicht_content_item_region', array('container' => $page))
                ->add('content_item_type', 'zicht_content_item_type', array('container' => $page))
            ;
        }
    }


    /**
     * @{inheritDoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('page');
    }


    /**
     * @{inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        return $listMapper
            ->addIdentifier('page')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'view'   => array(),
                        'edit'   => array(),
                        'delete' => array(),
                    )
                )
            );
    }
}
