<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\DependencyInjection\CompilerPass;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use \Symfony\Component\DependencyInjection\Reference;
use \Zicht\Util\Str;

/**
 * Generates admin services for all pages and content items, if the admin classes match the entity namespace structure.
 */
class GenerateAdminServicesCompilerPass implements CompilerPassInterface
{
    /**
     * @{inheritDoc}
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
                $serviceDefs[$type] = array();

                $def = $container->getDefinition($baseIds[$type]);

                foreach ($types[$type] as $entityClassName) {
                    $adminClassName = $naming($entityClassName);
                    if (class_exists($adminClassName)) {
                        $adminService = clone $def;
                        $adminService->setClass($adminClassName);

                        $tags = $adminService->getTags();
                        $tags['sonata.admin'][0]['show_in_dashboard'] = 0;
                        $tags['sonata.admin'][0]['label'] = Str::rstrip(Str::classname($entityClassName), 'Page');
                        $adminService->setTags($tags);

                        $id = $baseIds[$type]. '.' . Str::uscore(Str::classname($entityClassName));

                        $adminService->replaceArgument(0, $id);
                        $adminService->replaceArgument(1, $entityClassName);

                        $container->setDefinition($id, $adminService);

                        $serviceDefs[$type][]= $id;
                    }
                }
            }

            foreach ($serviceDefs['contentItem'] as $contentItemServiceId) {
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