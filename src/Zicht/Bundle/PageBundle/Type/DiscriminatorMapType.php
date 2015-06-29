<?php
/**
 * @author    Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Type;

use \Doctrine\Bundle\DoctrineBundle\Registry;
use \Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Symfony\Component\OptionsResolver\Options;
use \Zicht\Util\Str;

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
     * Construct with the registry specified.
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $registry
     */
    public function __construct(Registry $registry)
    {
        parent::__construct();
        $this->doctrine = $registry;
    }

    /**
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->doctrine->getManager();

        $choiceCallback = function(Options $options) use ($em) {
            $ret = array();
            foreach ($em->getClassMetadata($options['entity'])->discriminatorMap as $className) {
                $placeholder = 'content_item.type.' . strtolower(str_replace(' ', '_', Str::humanize(Str::classname($className))));
                $ret[$className] = $placeholder;
            }
            if (is_callable($options['choice_filter'])) {
                $ret = call_user_func($options['choice_filter'], $ret);
            }
            return $ret;
        };

        $resolver
            ->setRequired(array('entity'))
            ->setDefaults(
                array(
                    'choices' => $choiceCallback,
                    'choice_filter' => ''
                )
            )
        ;
    }


    /**
     * @{inheritDoc}
     */
    public function getParent()
    {
        return 'choice';
    }


    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return "zicht_discriminator_map";
    }
}