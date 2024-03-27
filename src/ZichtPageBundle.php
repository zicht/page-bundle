<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle;

use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle providing "page" -> "content-item" structure
 */
class ZichtPageBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $generatorPass = new DependencyInjection\CompilerPass\GenerateAdminServicesCompilerPass();

        // if the Sonata bundle is configured before this one, the compilerpass must be injected right before,
        // otherwise the generated services will not be recognized by Sonata.
        $idx = null;
        $beforeOptimizationPasses = $container->getCompilerPassConfig()->getBeforeOptimizationPasses();

        foreach ($beforeOptimizationPasses as $i => $pass) {
            if ($pass instanceof AddDependencyCallsCompilerPass) {
                $idx = $i;
                break;
            }
        }

        if (null !== $idx) {
            array_splice($beforeOptimizationPasses, $idx, 0, [$generatorPass]);
        } else {
            $beforeOptimizationPasses[] = $generatorPass;
        }
        $container->getCompilerPassConfig()->setBeforeOptimizationPasses($beforeOptimizationPasses);
    }
}
