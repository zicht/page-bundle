<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace {
    class aAdmin
    {
    }
    class bAdmin
    {
    }
    class cAdmin
    {
    }
    class dAdmin
    {
    }
    class eAdmin
    {
    }
    class fAdmin
    {
    }
}

namespace ZichtTest\Bundle\PageBundle\DependencyInjection\CompilerPass {
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Zicht\Bundle\PageBundle\DependencyInjection\CompilerPass\GenerateAdminServicesCompilerPass;

    class GenerateAdminServicesCompilerPassTest extends TestCase
    {
        public function testBareConfig()
        {
            $c = new ContainerBuilder();
            $c->setParameter('zicht_page.config', []);
            $pass = new GenerateAdminServicesCompilerPass();
            $pass->process($c);
            $this->expectNotToPerformAssertions();
        }

        public function testValidConfig()
        {
            $c = new \Symfony\Component\DependencyInjection\ContainerBuilder();

            $defs = [
                'zicht_page.page_manager' => new \Symfony\Component\DependencyInjection\Definition(),
                'page.admin' => new \Symfony\Component\DependencyInjection\Definition(),
                'ci.admin' => new \Symfony\Component\DependencyInjection\Definition(),
            ];
            $defs['page.admin']->addArgument(null);
            $defs['page.admin']->addArgument(null);

            $defs['ci.admin']->addArgument(null);
            $defs['ci.admin']->addArgument(null);

            $defs['zicht_page.page_manager']->addMethodCall('setPageTypes', [['a', 'b', 'c']]);
            $defs['zicht_page.page_manager']->addMethodCall('setContentItemTypes', [['d', 'e', 'f']]);

            foreach ($defs as $id => $def) {
                $c->setDefinition($id, $def);
            }
            $c->setParameter(
                'zicht_page.config',
                [
                'admin' => [
                    'base' => [
                        'page' => 'page.admin',
                        'contentItem' => 'ci.admin',
                    ],
                ],
                ]
            );
            $pass = new \Zicht\Bundle\PageBundle\DependencyInjection\CompilerPass\GenerateAdminServicesCompilerPass();
            $pass->process($c);
            $this->expectNotToPerformAssertions();
        }
    }
}
