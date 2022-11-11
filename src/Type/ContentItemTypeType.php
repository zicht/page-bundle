<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Type;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zicht\Bundle\PageBundle\Entity\ContentItem;
use Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use Zicht\Util\Str;

/**
 * Provides a "type" dropdown for creating content items, and a "edit link" for editing content items
 * that have their own admin.
 */
class ContentItemTypeType extends AbstractType
{
    private TranslatorInterface $translator;

    /**
     * @param string $contentItemClass
     */
    public function __construct($contentItemClass, TranslatorInterface $translator, Pool $sonata = null)
    {
        $this->contentItemClass = $contentItemClass;
        $this->translator = $translator;
        $this->sonata = $sonata;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'inherit_data' => true,
                'data_class' => $this->contentItemClass,
                'container' => '',
            ]
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['container']) {
            $page = $options['container'];
            $choiceFilter = function ($choices) use ($page) {
                if ($page instanceof ContentItemContainer && null !== $page->getContentItemMatrix()) {
                    $types = $page->getContentItemMatrix()->getTypes();

                    if (!$choices) {
                        return [];
                    }

                    if (is_string($choices)) {
                        $choices = [$choices => $choices];
                    }

                    $choices = \array_filter(
                        $choices,
                        function ($choice, $key) use ($types) {
                            return \in_array($key, $types);
                        },
                        ARRAY_FILTER_USE_BOTH
                    );

                    // Pre-translate so we can make the list ordered
                    $translated = [];
                    foreach ($choices as $className => $label) {
                        $translated[$this->translator->trans($label, [], 'admin')] = $className;
                    }
                    asort($translated);
                    return $translated;
                }
                return $choices;
            };
        } else {
            $choiceFilter = null;
        }
        $builder
            ->add(
                'convertToType',
                DiscriminatorMapType::class,
                [
                    'entity' => $this->contentItemClass,
                    'choice_filter' => $choiceFilter,
                ]
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->vars['sonata_admin']['admin'])) {
            $genericAdmin = $view->vars['sonata_admin']['admin'];

            $parentAdmin = $genericAdmin->getParent();

            /** @var ContentItem $subject */
            $subject = $form->getParent()->getData();

            $view->vars['type'] = null;
            $view->vars['edit_url'] = null;

            try {
                if ($subject->getId() && !is_null($subject) && $typeAdmin = $this->sonata->getAdminByClass(get_class($subject))) {
                    $view->vars['type'] = Str::humanize($subject->getType());
                    $childAdminCode = $parentAdmin->getCode() . '|' . $typeAdmin->getCode();
                    $childAdmin = $this->sonata->getAdminByAdminCode($childAdminCode);
                    $childAdmin->setRequest($genericAdmin->getRequest());

                    if ($subject && $subject->getPage() && $subject->getPage()->getId()) {
                        try {
                            $view->vars['edit_url'] = $childAdmin->generateObjectUrl('edit', $subject, ['childId' => $subject->getId()]);
                        } catch (InvalidParameterException $e) {
                            // 2.2 edit url not needed when generating other admins (this is done in the POST of the sonata_collection_type)
                        } catch (MissingMandatoryParametersException $e) {
                            // >= 2.3
                        } catch (\Exception $e) {
                        }
                    }
                }
            } catch (\RuntimeException $e) {
            }
        }
    }

    public function getBlockPrefix()
    {
        return 'zicht_content_item_type';
    }
}
