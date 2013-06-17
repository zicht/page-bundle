<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\PageBundle\Type;

class ContentItemRegionTypeTest extends \PHPUnit_Framework_TestCase
{
    function testConstruct()
    {
        $ret = new \Zicht\Bundle\PageBundle\Type\ContentItemRegionType(
            'foo',
            array('a' => 'a', 'b' => 'b')
        );

        $this->assertEquals('zicht_content_item_region', $ret->getName());
        return $ret;
    }


    /**
     * @param \Zicht\Bundle\PageBundle\Type\ContentItemRegionType $type
     * @depends testConstruct
     */
    function testSetDefaultOptions($type)
    {
        $optionsResolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $type->setDefaultOptions($optionsResolver);
        $options = $optionsResolver->resolve(array());
        $this->assertEquals(true, $options['virtual']);
        $this->assertEquals('foo', $options['data_class']);
        $this->assertEquals(array('a' => 'a', 'b' => 'b'), $options['default_regions']);

        return array($options, $type);
    }


    /**
     * @param \Zicht\Bundle\PageBundle\Type\ContentItemRegionType $type
     * @depends testSetDefaultOptions
     */
    function testBuildFormWithContainerSpecifiedWillAddRegionChoiceWithAvailableChoices($args)
    {
        list($options, $type) = $args;
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder('Zicht\Bundle\PageBundle\Model\ContentItemContainer')->getMock();
        $matrix = \Zicht\Bundle\PageBundle\Model\ContentItemMatrix::create()->region('x')->type('foo')->region('y')->type('bar');
        $container->expects($this->once())->method('getContentItemMatrix')->will($this->returnValue($matrix));
        $options['container']= $container;
        $builder->expects($this->once())->method('add')->with('region', 'choice', array('choices' => array('x' => 'x', 'y' => 'y')));
        $type->buildForm($builder, $options);
    }

    /**
     * @param \Zicht\Bundle\PageBundle\Type\ContentItemRegionType $type
     * @depends testSetDefaultOptions
     */
    function testBuildFormWithoutContainerSpecifiedWillAddDefaultRegions($args)
    {
        list($options, $type) = $args;
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')->disableOriginalConstructor()->getMock();
        $builder->expects($this->once())->method('add')->with('region', 'choice', array('choices' => array('a' => 'a', 'b' => 'b')));
        $type->buildForm($builder, $options);
    }

}