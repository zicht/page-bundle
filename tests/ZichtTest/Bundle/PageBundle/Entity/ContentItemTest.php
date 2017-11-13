<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\PageBundle\Entity;

abstract class Base extends \Zicht\Bundle\PageBundle\Entity\ContentItem
{
    private $commonProperty = 'foo-common';

    public function getCommonProperty()
    {
        return $this->commonProperty;
    }
}

class MyFooContentItem extends Base
{
    private $fooProperty  = 'foo';
}

class MyBarContentItem extends Base
{
    private $barProperty = 'bar';
}

class ContentItemTest extends \PHPUnit_Framework_TestCase
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
        \Zicht\Bundle\PageBundle\Entity\ContentItem::convert($foo, $bar);

        $this->assertEquals($foo->getCommonProperty(), $bar->getCommonProperty());
    }
}