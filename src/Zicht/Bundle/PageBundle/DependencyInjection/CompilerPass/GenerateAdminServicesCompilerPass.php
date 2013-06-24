<?php

namespace Zicht\Bundle\PageBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zicht\Util\Str;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
class GenerateAdminServicesCompilerPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $naming = function($fqEntityClassName) {
            return
                str_replace(
                    '\\Entity\\',
                    '\\Admin\\',
                    $fqEntityClassName
                ) . 'Admin';
        };


//        $container->get

        $config = $container->getParameter('zicht_page.config');
        if (!empty($config['admin'])) {
            $baseIds = $config['admin']['base'];

            $serviceDefs = array();
            $pageManagerDef = $container->getDefinition('zicht_page.page_manager');
            $types = array();

            foreach ($pageManagerDef->getMethodCalls() as $call) {
                switch ($call[0]) {
                    case 'setPageTypes':
                        $types['page'] = $call[1][0];
                        break;
                    case 'setContentItemTypes':
                        $types['contentItem'] = $call[1][0];
                        break;
                }
            }

            foreach (array('page', 'contentItem') as $type) {
                $def = $container->getDefinition($baseIds[$type]);

                foreach ($types[$type] as $entityClassName) {
                    $adminClassName = $naming($entityClassName);

                    if (class_exists($adminClassName)) {
                        $adminService = clone $def;
                        $adminService->setClass($adminClassName);

                        $tags = $adminService->getTags();
                        $tags['sonata.admin'][0]['show_in_dashboard'] = 0;
                        $tags['sonata.admin'][0]['label'] = \Zicht\Util\Str::rstrip($entityClassName, 'Page');
                        $adminService->setTags($tags);

                        $adminService->replaceArgument(1, $entityClassName);

                        $id = $baseIds[$type]. '.' . Str::uscore(Str::classname($entityClassName));
                        $container->setDefinition($id, $adminService);

                        $serviceDefs[$type][]= $id;
                    }
                }
            }

            foreach($serviceDefs['contentItem'] as $contentItemServiceId) {
                foreach ($serviceDefs['page'] as $pageServiceId) {
                    $container->getDefinition($pageServiceId)
                        ->addMethodCall(
                        'addChild',
                        array(
                            new Reference($contentItemServiceId)
                        )
                    );
                }
            }
        }
    }
}