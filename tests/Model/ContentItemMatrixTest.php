<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Foo\Bar {
    class a
    {
    }
    class b
    {
    }
}

namespace Baz\Bat {
    class c
    {
    }
}

namespace ZichtTest\Bundle\PageBundle\AdminMenu {
    use PHPUnit\Framework\TestCase;

    class ContentItemMatrixTest extends TestCase
    {
        public function testApi()
        {
            $matrix = \Zicht\Bundle\PageBundle\Model\ContentItemMatrix::create()
                ->region('left')
                    ->type(\Foo\Bar\a::class)
                    ->type(\Foo\Bar\b::class)
                ->region('right')
                    ->type(\Foo\Bar\b::class)
                    ->type(\Baz\Bat\c::class);

            $this->assertEquals(['left', 'right'], $matrix->getRegions());
            $this->assertEquals(['Foo\Bar\a', 'Foo\Bar\b', 'Baz\Bat\c'], $matrix->getTypes());

            $this->assertEquals([], $matrix->getRegions('qux'));
            $this->assertEquals(['left'], $matrix->getRegions('Foo\Bar\a'));
            $this->assertEquals(['left', 'right'], $matrix->getRegions('Foo\Bar\b'));
            $this->assertEquals(['right'], $matrix->getRegions('Baz\Bat\c'));

            $this->assertEquals(['Foo\Bar\a', 'Foo\Bar\b'], $matrix->getTypes('left'));
            $this->assertEquals(['Foo\Bar\b', 'Baz\Bat\c'], $matrix->getTypes('right'));

            $matrix->region('left', true);
            $this->assertEquals([], $matrix->getTypes('left'));
        }
    }
}
