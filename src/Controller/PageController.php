<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\PageBundle\Security\PageViewValidation;
use Zicht\Bundle\UrlBundle\Url\Provider as UrlProvider;

/**
 * Controller for public page actions
 *
 * @final in the future. You should not extend this PageController as this will result in duplication of
 *        its routes and will cause unexpected behaviour. Use the {@see PageControllerTrait} to use handy functionalities
 *        that were formerly available only in this PageController.
 */
class PageController extends AbstractController
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

    /**
     * Redirects to the page identified by the passed id.
     *
     * @param string $id
     * @Route("page/{id}/redirect", name="page_redirect")
     */
    public function redirectAction($id): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('zicht_page_page_view', ['id' => $id]));
    }

    /**
     * Redirects to the specified page. This is useful for posting an autocomplete ID, which in turn redirects to
     * the specified page.
     *
     * @Route("/goto")
     */
    public function gotoAction(Request $r): RedirectResponse
    {
        return $this->redirect(
            $this->urlProvider->url($this->getPageManager()->findForView($r->get('id')))
        );
    }

    /**
     * @param string $id
     * @Route("page/{id}")
     */
    public function viewAction(Request $request, $id): Response
    {
        if (get_class($this) !== __CLASS__) {
            trigger_deprecation('zicht/page-bundle', '8.2', 'Extending the "%s" controller is deprecated as it will be final in the future. Implement your own "viewAction()" and/or use "%s" functionalities.', __CLASS__, PageControllerTrait::class);
        }

        $page = $this->pageManager->findForView($id);

        $this->isViewActionAllowed($page);

        $forward = $this->shouldForwardControllerPage($page, $request);
        if ($forward instanceof Response) {
            return $forward;
        }

        return $this->renderPage($page);
    }
}
