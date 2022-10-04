<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
            'zicht_url.provider' => UrlProvider::class,
            'zicht_page.controller.view_validator' => PageViewValidation::class,
            ]
        );
    }

    /**
     * Redirects to the page identified by the passed id.
     *
     * @param string $id
     * @return Response
     * @Route("page/{id}/redirect", name="page_redirect")
     */
    public function redirectAction($id)
    {
        return new RedirectResponse($this->generateUrl('zicht_page_page_view', ['id' => $id]));
    }

    /**
     * Redirects to the specified page. This is useful for posting an autocomplete ID, which in turn redirects to
     * the specified page.
     *
     * @return Response
     * @Route("/goto")
     */
    public function gotoAction(Request $r)
    {
        return $this->redirect(
            $this->get('zicht_url.provider')->url($this->getPageManager()->findForView($r->get('id')))
        );
    }

    /**
     * @param string $id
     * @return Response
     * @Route("page/{id}")
     */
    public function viewAction(Request $request, $id)
    {
        $pageManager = $this->getPageManager();
        $page = $pageManager->findForView($id);

        if (null !== ($validator = $this->getViewActionValidator())) {
            try {
                $validator->validate($page);
            } catch (AccessDeniedException $e) {
                // AccessDeniedException will cause a redirect to the login page, so we're throwing
                // an AccessDeniedHttpException instead to make Symfony return a 403 response
                // (don't pass the previous exception, as this will again cause a redirect to the login page)
                throw new AccessDeniedHttpException($e->getMessage());
            }
        }

        if ($page instanceof ControllerPageInterface && $page->getController() !== null) {
            return $this->forward(
                $page->getController(),
                (array)$page->getControllerParameters()
                + [
                    'parameters' => $request->query->all(),
                    '_locale' => $request->attributes->get('_locale'),
                    '_internal_url' => $request->attributes->get('_internal_url'),
                ],
                $request->query->all()
            );
        }

        return $this->renderPage($page);
    }

    /**
     * @return ViewValidationInterface|null
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
     * @param array $vars
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderPage(PageInterface $page, $vars = [])
    {
        return $this->render(
            $this->getPageManager()->getTemplate($page),
            $vars + [
                'page' => $page,
                'id' => $page->getId(),
            ]
        );
    }
}
