<?php declare(strict_types=1);

namespace ZichtTest\Bundle\PageBundle\Type;

use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zicht\Bundle\PageBundle\Type\ContentItemTypeType;

class ContentItemTypeTypeTest extends TestCase
{
    public function testConstruct()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $ret = new ContentItemTypeType('foo', $translator, null);
        $this->assertEquals('zicht_content_item_type', $ret->getBlockPrefix());
        return $ret;
    }

    /**
     * @depends testConstruct
     * @param mixed $type
     */
    public function testSetDefaultOptions($type)
    {
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $this->assertTrue($options['inherit_data']);
        $this->assertEquals('foo', $options['data_class']);
    }
}
