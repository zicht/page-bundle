<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Zicht\Bundle\PageBundle\Entity\ContentItem;
use Zicht\Bundle\PageBundle\Type\ContentItemRegionType;
use Zicht\Bundle\PageBundle\Type\ContentItemTypeType;

/**
 * Admin for content items.
 *
 * @template T of ContentItem
 * @extends AbstractAdmin<T>
 */
class ContentItemAdmin extends AbstractAdmin
{
    /**
     * {@inheritDoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        if (!$this->isChild()) {
            $form->add('page');
        } else {
            $page = $this->getParentFieldDescription()->getAdmin()->getSubject();

            // This fixes a weird bug where the router does not get the correct parameters for the containing page.
            $this->getParentFieldDescription()->setOption('link_parameters', array('id' => $page->getId()));

            $form
                ->add('weight')
                ->add('internalName', TextType::class, array('disabled' => true, 'required' => false, 'attr' => ['read_only' => true]))
                ->add('content_item_region', ContentItemRegionType::class, array('container' => $page))
                ->add('content_item_type', ContentItemTypeType::class, array('container' => $page));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('page');
    }


    /**
     * {@inheritDoc}
     */
    public function configureListFields(ListMapper $list)
    {
        return $list
            ->addIdentifier('page')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    )
                )
            );
    }
}
