<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use \Sonata\AdminBundle\Controller\CRUDController;

/**
 * Controller used for the ContentItem detail CRUD.
 *
 * This redirects to the "edit page" URL if the "list" is displayed within the context of a page. This makes sure
 * the breadcrumbs work (more or less) as expected.
 */
class ContentItemDetailCRUDController extends CRUDController
{
    /**
     * @{inheritDoc}
     */
    public function listAction()
    {
        if (($parent = $this->admin->getParent()) && ($container = $parent->getSubject())) {
            return $this->redirect(
                $parent->generateObjectUrl('edit', $container)
            );
        }
        return parent::listAction();
    }
}