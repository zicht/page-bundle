<?php
/**
 * @copyright Zicht Online <https://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Zicht\Util\Str;

/**
 * Provides a dropdown to select a type specified by doctrine's discriminator map.
 */
class DiscriminatorMapType extends ChoiceType
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine = null;

    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $registry
     */
    public function __construct(Registry $registry)
    {
        parent::__construct();
        $this->doctrine = $registry;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->doctrine->getManager();

        $choiceCallback = static function (Options $options) use ($em) {
            $choices = [];
            foreach ($em->getClassMetadata($options['entity'])->discriminatorMap as $className) {
                if ($className === $options['entity']) {
                    continue;
                }

                $label = 'content_item.type.' . strtolower(str_replace(' ', '_', Str::humanize($className)));
                $choices[$label] = $className;
            }

            if (is_callable($options['choice_filter'])) {
                $choices = call_user_func($options['choice_filter'], $choices);
            }

            return $choices;
        };

        $resolver
            ->setRequired(['entity'])
            ->setDefaults(
                [
                    'choices' => $choiceCallback,
                    'choice_filter' => null,
                    'translation_domain' => 'admin',
                ]
            )
            ->setAllowedTypes('choices', ['array'])
            ->setAllowedTypes('choice_filter', ['null', 'callable']);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'zicht_discriminator_map';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "zicht_discriminator_map";
    }
}
