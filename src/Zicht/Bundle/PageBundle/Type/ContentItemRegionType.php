<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Type;

use \Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\Form\AbstractType;
use \Zicht\Bundle\PageBundle\Model\ContentItemContainer;


/**
 * Provides a type for selecting the region of the content item.
 */
class ContentItemRegionType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param string $contentItemClassName
     * @param array $defaultRegions
     */
    public function __construct($contentItemClassName, array $defaultRegions = array())
    {
        $this->contentItemClassName = $contentItemClassName;
        $this->defaultRegions = $defaultRegions;
    }


    /**
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            array(
                'virtual'    => true,
                'data_class' => $this->contentItemClassName,
                'container'  => '',
                'default_regions' => $this->defaultRegions
            )
        );
    }


    /**
     * @{inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $container = $options['container'];
        if ($container && $container instanceof ContentItemContainer && null !== $container->getContentItemMatrix()) {
            $choices = array();
            foreach ($container->getContentItemMatrix()->getRegions() as $c) {
                $choices[$c] = $c;
            }
            $builder->add('region', 'choice', array('choices' => $choices));
        } else {
            $builder->add('region', 'choice', array('choices' => $options['default_regions']));
        }
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'zicht_content_item_region';
    }
}