<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\PageBundle\Type;

class ContentItemTypeTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        $ret = new \Zicht\Bundle\PageBundle\Type\ContentItemTypeType('foo', $pool);
        $this->assertEquals('zicht_content_item_type', $ret->getBlockPrefix());
        return $ret;
    }


    /**
     * @depends testConstruct
     */
    public function testSetDefaultOptions($type)
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(array());

        $this->assertTrue($options['inherit_data']);
        $this->assertEquals('foo', $options['data_class']);
    }
}
