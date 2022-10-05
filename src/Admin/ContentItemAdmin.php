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
 * @template T of ContentItem
 * @extends AbstractAdmin<T>
 */
class ContentItemAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        if (!$this->isChild()) {
            $form->add('page');
        } else {
            $page = $this->getParentFieldDescription()->getAdmin()->getSubject();

            // This fixes a weird bug where the router does not get the correct parameters for the containing page.
            $this->getParentFieldDescription()->setOption('link_parameters', ['id' => $page->getId()]);

            $form
                ->add('weight')
                ->add('internalName', TextType::class, ['disabled' => true, 'required' => false, 'attr' => ['read_only' => true]])
                ->add('content_item_region', ContentItemRegionType::class, ['container' => $page])
                ->add('content_item_type', ContentItemTypeType::class, ['container' => $page]);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('page');
    }

    public function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('page', null, ['route' => ['name' => 'edit']])
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
}
