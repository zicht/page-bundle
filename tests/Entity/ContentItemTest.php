<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Entity;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\PageBundle\Entity\ContentItem;

abstract class Base extends ContentItem
{
    private $commonProperty = 'foo-common';

    public function getCommonProperty()
    {
        return $this->commonProperty;
    }
}

class MyFooContentItem extends Base
{
}

class MyBarContentItem extends Base
{
}

class ContentItemTest extends TestCase
{
    public function testGetShortType()
    {
        $item = new MyFooContentItem();
        $this->assertEquals('my-foo', $item->getShortType());

        $item = new MyBarContentItem();
        $this->assertEquals('my-bar', $item->getShortType());
    }

    public function testConversion()
    {
        $foo = new MyFooContentItem();
        $bar = new MyBarContentItem();
        ContentItem::convert($foo, $bar);

        $this->assertEquals($foo->getCommonProperty(), $bar->getCommonProperty());
    }
}
