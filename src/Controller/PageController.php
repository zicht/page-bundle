<?php

namespace Zicht\Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\PageBundle\Security\PageViewValidation;
use Zicht\Bundle\UrlBundle\Url\Provider as UrlProvider;

/**
 * Controller for public page actions
 *
 * Final: You should not extend this PageController as this will result in duplication of
 *        its routes and will cause unexpected behaviour. Use the {@see PageControllerTrait} to use handy functionalities
 *        that were previously available only in this PageController.
 */
final class PageController extends AbstractController
{
    use PageControllerTrait;

    private PageManager $pageManager;

    private UrlProvider $urlProvider;

    private PageViewValidation $pageViewValidation;

    public function __construct(PageManager $pageManager, UrlProvider $urlProvider, PageViewValidation $pageViewValidation)
    {
        $this->pageManager = $pageManager;
        $this->urlProvider = $urlProvider;
        $this->pageViewValidation = $pageViewValidation;
    }

    /** @deprecated Use the injected services instead of getting them from the container. */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'zicht_url.provider' => UrlProvider::class,
                'zicht_page.controller.view_validator' => PageViewValidation::class,
                'zicht_page.page_manager' => PageManager::class,
            ]
        );
    }

    /** Redirects to the page identified by the passed id. */
    #[Route('page/{id}/redirect', name: 'page_redirect')]
    public function redirectAction(int $id): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('zicht_page_page_view', ['id' => $id]));
    }

    /** Redirects to the specified page. This is useful for posting an autocomplete ID, which in turn redirects to the specified page. */
    #[Route('/goto')]
    public function gotoAction(Request $request): RedirectResponse
    {
        return $this->redirect($this->urlProvider->url($this->getPageManager()->findForView($request->get('id'))));
    }

    #[Route('page/{id}')]
    public function viewAction(Request $request, int $id): Response
    {
        $page = $this->pageManager->findForView($id);

        $this->isViewActionAllowed($page);

        $forward = $this->shouldForwardControllerPage($page, $request);
        if ($forward instanceof Response) {
            return $forward;
        }

        return $this->renderPage($page);
    }
}
