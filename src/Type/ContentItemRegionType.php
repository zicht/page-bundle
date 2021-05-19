<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
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
    protected $defaultRegions = [];

    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    /**
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
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'inherit_data' => true,
                'data_class' => $this->contentItemClassName,
                'container' => null,
                'default_regions' => $this->defaultRegions,
                'translation_domain' => 'admin',
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $options['default_regions'];
        $container = $options['container'];
        if ($container && $container instanceof ContentItemContainer && $container->getContentItemMatrix() !== null) {
            $choices = $container->getContentItemMatrix()->getRegions();
        }

        $builder->add(
            'region',
            ChoiceType::class,
            [
                'choices' => $choices,
                'choice_label' => static function ($choice) {
                    return 'choice.content_item_region.' . str_replace('-', '_', $choice);
                },
                'translation_domain' => 'admin',
                'placeholder' => $this->translator->trans('content_item_region.empty_value', [], 'admin'),
                'constraints' => [new Assert\NotBlank()],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $matrix = array();
        $fullmatrix = $options['container']->getContentItemMatrix()->getMatrix();

        foreach ($fullmatrix as $region => $classNames) {
            foreach ($classNames as $className) {
                $snakeCasedClassName = strtolower(str_replace(' ', '_', Str::humanize($className)));
                $placeholder = sprintf('content_item.type.%s', $snakeCasedClassName);
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

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'zicht_content_item_region';
    }
}
