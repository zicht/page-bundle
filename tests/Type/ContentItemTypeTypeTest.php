<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Type;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentItemTypeTypeTest extends TestCase
{
    public function testConstruct()
    {
        $this->markTestSkipped('Marked skipped until resolving mocking final class Pool');
        $pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        $translator = $this->createMock(TranslatorInterface::class);
        $ret = new \Zicht\Bundle\PageBundle\Type\ContentItemTypeType('foo', $translator, $pool);
        $this->assertEquals('zicht_content_item_type', $ret->getBlockPrefix());
        return $ret;
    }

    /**
     * @depends testConstruct
     * @param mixed $type
     */
    public function testSetDefaultOptions($type)
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $this->assertTrue($options['inherit_data']);
        $this->assertEquals('foo', $options['data_class']);
    }
}
