<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use Zicht\Util\Str;

/**
 * Provides a type for selecting the region of the content item.
 */
class ContentItemRegionType extends AbstractType
{
    /**
     * @var string $contentItemClassName
     */
    protected $contentItemClassName;

    /**
     * @var array $defaultRegions
     */
    protected $defaultRegions = array();

    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param string $contentItemClassName
     * @param array $defaultRegions
     * @param TranslatorInterface $translator
     */
    public function __construct($contentItemClassName, array $defaultRegions, TranslatorInterface $translator)
    {
        $this->contentItemClassName = $contentItemClassName;
        $this->defaultRegions = $defaultRegions;
        $this->translator = $translator;
    }

    /**
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            array(
                'inherit_data'    => true,
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
        if ($container
            && $container instanceof ContentItemContainer
            && (null !== ($matrix = $container->getContentItemMatrix()))
        ) {
            $choices = array();
            foreach ($matrix->getRegions() as $c) {
                $choices[$c] = $c;
            }
            $builder->add(
                'region',
                'choice',
                array(
                    'choices' => $choices,
                    'translation_domain' => 'admin',
                    'empty_value' => $this->translator->trans('content_item_region.empty_value', array(), 'admin')
                )
            );
        } else {
            $builder->add('region', 'choice', array('choices' => $options['default_regions'], 'translation_domain' => 'admin'));
        }
    }

    /**
     * @{inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $matrix = array();
        $fullmatrix = $options['container']->getContentItemMatrix()->getMatrix();

        foreach ($fullmatrix as $region => $classNames) {
            foreach ($classNames as $className) {
                $placeholder = 'content_item.type.' . strtolower(str_replace(' ', '_', Str::humanize(Str::classname($className))));
                $matrix[$region][$className] = $this->translator->trans($placeholder, array(), 'admin', 'nl');
            }
        }

        // todo: the matrix says something over both the DiscriminatorMapType and the ContentItemRegionType,
        // hence it should be moved to the ContentItemTypeType
        $view->vars['matrix'] = json_encode($matrix);
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
