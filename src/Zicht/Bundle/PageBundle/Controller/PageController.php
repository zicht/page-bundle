<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\RedirectResponse;

use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use \Zicht\Bundle\PageBundle\Entity\ControllerPageInterface;
use \Symfony\Component\HttpFoundation\Request;

/**
 * Controller for public page actions
 */
class PageController extends AbstractController
{
    /**
     * Redirects to the page identified by the passed id.
     *
     * @param string $id
     * @return Response
     *
     * @Route("page/{id}/redirect", name="page_redirect")
     */
    public function redirectAction($id)
    {
        return new RedirectResponse($this->generateUrl('zicht_page_page_view', array('id' => $id)));
    }


    /**
     * Redirects to the specified page. This is useful for posting an autocomplete ID, which in turn redirects to
     * the specified page.
     *
     * @param Request $r
     * @return Response
     *
     * @Route("/goto")
     */
    public function gotoAction(Request $r)
    {
        return $this->redirect(
            $this->get('zicht_url.provider')->url($this->getPageManager()->findForView($r->get('id')))
        );
    }


    /**
     * View a page.
     *
     * @param string $id
     * @param mixed $query
     * @return Response
     *
     * @Route("page/{id}/{query}", defaults={"query":""}, requirements={"query":".+"})
     */
    public function viewAction($id, $query = null)
    {
        /** @var $pageManager \Zicht\Bundle\PageBundle\Manager\PageManager */
        $pageManager = $this->getPageManager();

        $page = $pageManager->findForView($id);

        if ($page instanceof ControllerPageInterface) {
            return $this->forward($page->getController(), (array)$page->getControllerParameters());
        }

        return $this->render(
            $pageManager->getTemplate($page),
            array(
                'page' => $page,
                'query' => $query,
                'id' => $id,
            )
        );
    }
}