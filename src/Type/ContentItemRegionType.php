<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use Zicht\Util\Str;

/**
 * Provides a type for selecting the region of the content item.
 */
class ContentItemRegionType extends AbstractType
{
    /** @var string */
    protected $contentItemClassName;

    /** @var array */
    protected $defaultRegions = [];

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param string $contentItemClassName
     */
    public function __construct($contentItemClassName, array $defaultRegions, TranslatorInterface $translator)
    {
        $this->contentItemClassName = $contentItemClassName;
        $this->defaultRegions = $defaultRegions;
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'inherit_data' => true,
                'data_class' => $this->contentItemClassName,
                'container' => '',
                'default_regions' => $this->defaultRegions,
                'translation_domain' => 'admin',
            ]
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $container = $options['container'];
        if ($container
            && $container instanceof ContentItemContainer
            && (null !== ($matrix = $container->getContentItemMatrix()))
        ) {
            $choices = [];
            foreach ($matrix->getRegions() as $r) {
                $choices[$r] = $r;
            }
            $builder->add(
                'region',
                ChoiceType::class,
                [
                    'choices' => $choices,
                    'translation_domain' => 'admin',
                    'placeholder' => $this->translator->trans('content_item_region.empty_value', [], 'admin'),
                ]
            );
        } else {
            $builder->add(
                'region',
                ChoiceType::class,
                [
                    'choices' => $options['default_regions'],
                    'translation_domain' => 'admin',
                ]
            );
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $matrix = [];
        $fullmatrix = $options['container']->getContentItemMatrix()->getMatrix();

        foreach ($fullmatrix as $region => $classNames) {
            foreach ($classNames as $className) {
                $snakeCasedClassName = strtolower(str_replace(' ', '_', Str::humanize($className)));
                $placeholder = sprintf('content_item.type.%s', $snakeCasedClassName);
                $matrix[$region][$className] = $this->translator->trans($placeholder, [], 'admin', 'nl');
            }
        }

        // todo: the matrix says something over both the DiscriminatorMapType and the ContentItemRegionType,
        // hence it should be moved to the ContentItemTypeType
        $view->vars['matrix'] = json_encode($matrix);
    }

    public function getBlockPrefix()
    {
        return 'zicht_content_item_region';
    }
}
