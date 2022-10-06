<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Assets {
    class foo
    {
    }
    class bar
    {
    }
}

namespace ZichtTest\Bundle\PageBundle\Type {
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

    class ContentItemRegionTypeTest extends TestCase
    {
        public function testConstruct()
        {
            $ret = new \Zicht\Bundle\PageBundle\Type\ContentItemRegionType(
                'foo',
                ['a' => 'a', 'b' => 'b'],
                $this->createMock('Symfony\Contracts\Translation\TranslatorInterface')
            );

            $this->assertEquals('zicht_content_item_region', $ret->getBlockPrefix());
            return $ret;
        }

        /**
         * @param \Zicht\Bundle\PageBundle\Type\ContentItemRegionType $type
         * @depends testConstruct
         */
        public function testSetDefaultOptions($type)
        {
            $optionsResolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
            $type->configureOptions($optionsResolver);
            $options = $optionsResolver->resolve([]);
            $this->assertEquals(true, $options['inherit_data']);
            $this->assertEquals('foo', $options['data_class']);
            $this->assertEquals(['a' => 'a', 'b' => 'b'], $options['default_regions']);

            return [$options, $type];
        }

        /**
         * @param mixed $args
         * @depends testSetDefaultOptions
         */
        public function testBuildFormWithContainerSpecifiedWillAddRegionChoiceWithAvailableChoices($args)
        {
            list($options, $type) = $args;
            $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')->disableOriginalConstructor()->getMock();
            $container = $this->getMockBuilder('Zicht\Bundle\PageBundle\Model\ContentItemContainer')->getMock();
            $matrix = \Zicht\Bundle\PageBundle\Model\ContentItemMatrix::create()
                ->region('x')
                    ->type(\ZichtTest\Bundle\PageBundle\Assets\foo::class)
                ->region('y')
                    ->type(\ZichtTest\Bundle\PageBundle\Assets\bar::class);
            $container->expects($this->once())->method('getContentItemMatrix')->will($this->returnValue($matrix));
            $options['container'] = $container;
            $builder->expects($this->once())->method('add')->with('region', ChoiceType::class, ['choices' => ['x' => 'x', 'y' => 'y'], 'translation_domain' => 'admin', 'placeholder' => null]);
            $type->buildForm($builder, $options);
        }

        /**
         * @param mixed $args
         * @depends testSetDefaultOptions
         */
        public function testBuildFormWithoutContainerSpecifiedWillAddDefaultRegions($args)
        {
            list($options, $type) = $args;
            $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')->disableOriginalConstructor()->getMock();
            $builder->expects($this->once())->method('add')->with('region', ChoiceType::class, ['choices' => ['a' => 'a', 'b' => 'b'], 'translation_domain' => 'admin']);
            $type->buildForm($builder, $options);
        }
    }
}
