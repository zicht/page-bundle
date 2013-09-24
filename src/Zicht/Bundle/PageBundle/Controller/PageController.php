<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use \Symfony\Component\HttpFoundation\Response;
use \Zicht\Bundle\PageBundle\Model\PageInterface;
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
     * @return Response
     *
     * @Route("page/{id}")
     */
    public function viewAction($id)
    {
        /** @var $pageManager \Zicht\Bundle\PageBundle\Manager\PageManager */
        $pageManager = $this->getPageManager();

        $page = $pageManager->findForView($id);

        if ($page instanceof ControllerPageInterface) {
            return $this->forward(
                $page->getController(),
                (array)$page->getControllerParameters()
                + array(
                    'parameters' => $this->getRequest()->query->all()
                )
                + $this->getRequest()->attributes->all()
            );
        }

        return $this->renderPage($page);
    }


    /**
     * Render a page with the specified additional template variables.
     *
     * @param \Zicht\Bundle\PageBundle\Model\PageInterface $page
     * @param array $vars
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderPage(PageInterface $page, $vars = array())
    {
        return $this->render(
            $this->getPageManager()->getTemplate($page),
            $vars + array(
                'page' => $page,
                'id' => $page->getId(),
            )
        );
    }
}