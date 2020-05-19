<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zicht\Bundle\PageBundle\Entity\ControllerPageInterface;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Bundle\PageBundle\Model\ViewValidationInterface;
use Zicht\Bundle\PageBundle\Security\PageViewValidation;
use Zicht\Bundle\UrlBundle\Url\Provider as UrlProvider;

/**
 * Controller for public page actions
 */
class PageController extends AbstractController
{
    /** {@inheritDoc} */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'zicht_url.provider' => UrlProvider::class,
            'zicht_page.controller.view_validator' => PageViewValidation::class,
        ]);
    }

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
     * @param Request $request
     * @param string $id
     * @return Response
     *
     * @Route("page/{id}")
     */
    public function viewAction(Request $request, $id)
    {
        $pageManager = $this->getPageManager();
        $page = $pageManager->findForView($id);

        if (null !== ($validator = $this->getViewActionValidator())) {
            $validator->validate($page);
        }

        if ($page instanceof ControllerPageInterface) {
            return $this->forward(
                $page->getController(),
                (array)$page->getControllerParameters()
                + array(
                    'parameters' => $request->query->all(),
                    '_locale'       => $request->attributes->get('_locale'),
                    '_internal_url' => $request->attributes->get('_internal_url'),
                ),
                $request->query->all()
            );
        }

        return $this->renderPage($page);
    }

    /**
     * @return null|ViewValidationInterface
     */
    protected function getViewActionValidator()
    {
        if (($validator = $this->get('zicht_page.controller.view_validator')) instanceof ViewValidationInterface) {
            return $validator;
        }
        return null;
    }

    /**
     * Render a page with the specified additional template variables.
     *
     * @param PageInterface $page
     * @param array $vars
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderPage(PageInterface $page, $vars = array())
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
