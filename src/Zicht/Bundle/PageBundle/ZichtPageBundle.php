<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle providing "page" -> "content-item" structure
 */
class ZichtPageBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            new DependencyInjection\CompilerPass\GenerateAdminServicesCompilerPass(),
            \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION
        );
    }
}
