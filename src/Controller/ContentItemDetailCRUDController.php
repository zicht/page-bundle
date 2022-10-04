<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This redirects to the "edit page" URL if the "list" is displayed within the context of a page. This makes sure
 * the breadcrumbs work (more or less) as expected.
 */
class ContentItemDetailCRUDController extends CRUDController
{
    public function listAction(Request $request): Response
    {
        try {
            $parent = $this->admin->getParent();
            if ($parent && $container = $parent->getSubject()) {
                return $this->redirect(
                    $parent->generateObjectUrl('edit', $container)
                );
            }
        } catch (\LogicException $exception) {
        } finally {
            return parent::listAction($request);
        }
    }
}
