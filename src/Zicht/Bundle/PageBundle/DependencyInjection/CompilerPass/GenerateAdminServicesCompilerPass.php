<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use Zicht\Util\Str;

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
        $config = $container->getParameter('zicht_page.config');
        if (empty($config['admin'])) {
            return;
        }

        $config = $container->getParameter('zicht_page.config');

        $baseIds = $config['admin']['base'];

        $serviceDefinitions = array();
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
            $serviceDefinitions[$type] = array();

            $def = $container->getDefinition($baseIds[$type]);

            foreach ($types[$type] as $entityClassName) {
                $adminClassName = $this->renderAdminClassName($entityClassName);
                if (!class_exists($adminClassName)) {
                    throw new \Exception(
                        sprintf(
                            'The PageBundle was unable to create a service definition for %s because the associated class %s was not found',
                            $entityClassName,
                            $adminClassName
                        )
                    );
                }

                $adminService = clone $def;
                $adminService->setClass($adminClassName);

                $id = Str::systemize($adminClassName, '.');

                $tags = $adminService->getTags();
                $tags['sonata.admin'][0]['show_in_dashboard'] = 0;
                $tags['sonata.admin'][0]['label'] = $id;
                $adminService->setTags($tags);

                $adminService->replaceArgument(0, $id);
                $adminService->replaceArgument(1, $entityClassName);

                $container->setDefinition($id, $adminService);

                $serviceDefinitions[$type][] = $id;
            }
        }

        foreach ($serviceDefinitions['page'] as $pageServiceId) {
            $pageDef = $container->getDefinition($pageServiceId);
            $pageClassName = $pageDef->getArgument(1);

            if (!is_a($pageClassName, 'Zicht\Bundle\PageBundle\Model\ContentItemContainer', true)) {
                continue;
            }

            /** @var ContentItemContainer $instance */
            $instance = new $pageClassName;
            $contentItemClassNames = $instance->getContentItemMatrix()->getTypes();
            foreach ($serviceDefinitions['contentItem'] as $contentItemServiceId) {
                $contentItemDefinition = $container->getDefinition($contentItemServiceId);

                if (in_array($contentItemDefinition->getArgument(1), $contentItemClassNames)) {
                    $pageDef->addMethodCall(
                        'addChild',
                        array(new Reference($contentItemServiceId))
                    );
                }
            }
        }
    }

    /**
     * @param string $fullyQualifiedClassName
     *
     * @return string
     */
    private function renderAdminClassName($fullyQualifiedClassName)
    {
        return sprintf(
            '%s%s',
            str_replace('\\Entity\\', '\\Admin\\', $fullyQualifiedClassName),
            'Admin'
        );
    }
}
