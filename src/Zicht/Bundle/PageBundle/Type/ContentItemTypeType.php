<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Type;

use \Sonata\AdminBundle\Admin\Pool;

use \Symfony\Component\Form;
use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\Form\FormView;
use \Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use \Symfony\Component\Form\FormInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Symfony\Component\OptionsResolver\Options;

use Symfony\Component\Routing\Exception\InvalidParameterException;
use \Zicht\Bundle\PageBundle\Entity\ContentItem;
use \Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use \Zicht\Util\Str;


/**
 * Provides a "type" dropdown for creating content items, and a "edit link" for editing content items
 * that have their own admin.
 */
class ContentItemTypeType extends AbstractType
{
    /**
     * Constructor
     *
     * @param \Sonata\AdminBundle\Admin\Pool $sonata
     * @param string $contentItemClass
     */
    public function __construct(Pool $sonata, $contentItemClass)
    {
        $this->sonata = $sonata;
        $this->contentItemClass = $contentItemClass;
    }


    /**
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'virtual' => true,
                    'data_class' => $this->contentItemClass,
                    'container' => '',
                )
            )
        ;
    }


    /**
     * @{inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['container']) {
            $page = $options['container'];
            $choiceFilter = function ($choices) use($page) {
                $ret = array();
                if ($page instanceof ContentItemContainer && null !== $page->getContentItemMatrix()) {
                    $types = $page->getContentItemMatrix()->getTypes();

                    foreach ($choices as $className => $name) {
                        if (in_array($className, $types)) {
                            $ret[$className] = $name;
                        }
                    }
                } else {
                    return $choices;
                }
                return $ret;
            };
        } else {
            $choiceFilter = null;
        }
        $builder
            ->add(
                'convertToType',
                'zicht_discriminator_map',
                array(
                    'entity' => $this->contentItemClass,
                    'choice_filter' => $choiceFilter
                )
            )
        ;

        // Add a listener that creates the correct ContentItem instance if the type is not yet
        // defined (i.e., on "add")
        $builder->getParent()->addEventListener(
            Form\FormEvents::BIND,
            function(Form\FormEvent $e) {
                $data = $e->getData();
                if (null === $data) {
                    return;
                }
                $type = $data->getConvertToType();
                if (!$data->getId() && $type !== get_class($data)) {
                    $item = new $type;
                    ContentItem::convert($data, $item);
                    $e->setData($item);
                }
            },
            64
        );
    }


    /**
     * @{inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $genericAdmin = $view->vars['sonata_admin']['admin'];
        $parentAdmin = $genericAdmin->getParent();

        $subject = $form->getParent()->getData();

        $view->vars['type'] = null;
        $view->vars['edit_url'] = null;

        if ($subject->getId()) {
            $view->vars['type']= Str::humanize(Str::classname($subject->getConvertToType()));

            if ($typeAdmin = $this->sonata->getAdminByClass(get_class($subject))) {
                $childAdmin = $this->sonata->getAdminByAdminCode($parentAdmin->getCode() . '|' . $typeAdmin->getCode());
                $childAdmin->setRequest($genericAdmin->getRequest());

                if ($subject && $subject->getId() && $subject->getPage() && $subject->getPage()->getId()) {
                    try {
                        $view->vars['edit_url'] = $childAdmin->generateObjectUrl('edit', $subject);
                    } catch (InvalidParameterException $e) {
                        //edit url not needed when generating other admins (this is done in the POST of the sonata_collection_type)
                    }
                }
            }
        }
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'zicht_content_item_type';
    }
}