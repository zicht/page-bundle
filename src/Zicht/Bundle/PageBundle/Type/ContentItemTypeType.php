<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Type;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Zicht\Bundle\PageBundle\Entity\ContentItem;
use Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use Zicht\Util\Str;

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
    public function __construct($contentItemClass, Pool $sonata = null)
    {
        $this->contentItemClass = $contentItemClass;
        $this->sonata = $sonata;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'inherit_data' => true,
                'data_class' => $this->contentItemClass,
                'container' => '',
                'translation_domain' => 'admin'
            )
        );
    }

    /**
     * @{inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['container']) {
            $page = $options['container'];
            $choiceFilter = function ($choices) use ($page) {
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
                // As of SF2.8
                return array_flip($ret);
            };
        } else {
            $choiceFilter = null;
        }
        $builder
            ->add(
                'convertToType',
                DiscriminatorMapType::class,
                array(
                    'entity' => $this->contentItemClass,
                    'choice_filter' => $choiceFilter,
                )
            );
    }


    /**
     * @{inheritDoc}
     */
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
                $isPersistedEntity = $subject->getId();
                // $subject->getId() does not work when the `zicht/versioning-bundle` is used, as those ContentItem entities do exist but do *not* have an id when editing a non-active version.
                if ($this->sonata->getContainer()->has('zicht_versioning.manager') && $this->sonata->getContainer()->get('zicht_versioning.manager')->isManaged($subject->getPage())) {
                    if (method_exists($subject, 'getWeight')) {
                        // sonata adds new entries with a weight of 0.
                        $isPersistedEntity = $subject->getWeight() > 0 ? true : false;
                    } else {
                        // unknown how to determine this when the weight is not available. possibly update VersioningBundle to tell us this.
                        throw new \LogicException('Unable to determine the persisted state of this contentitem');
                    }
                }
                if ($isPersistedEntity && !is_null($subject) && $typeAdmin = $this->sonata->getAdminByClass(get_class($subject))) {
                    $view->vars['type'] = Str::humanize($subject->getType());
                    $childAdmin = $this->sonata->getAdminByAdminCode($parentAdmin->getCode() . '|' . $typeAdmin->getCode());
                    $childAdmin->setRequest($genericAdmin->getRequest());

                    if ($subject && $subject->getPage() && $subject->getPage()->getId()) {
                        try {
                            $view->vars['edit_url'] = $childAdmin->generateObjectUrl('edit', $subject, ['childId' => $subject->getId()]);
                        } catch (InvalidParameterException $e) {
                            //2.2 edit url not needed when generating other admins (this is done in the POST of the sonata_collection_type)
                        } catch (MissingMandatoryParametersException $e) {
                            //>= 2.3
                        } catch (\Exception $e) {
                        }
                    }
                }
            } catch (\RuntimeException $e) {
            }
        }
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'zicht_content_item_type';
    }
}
