<?php declare(strict_types=1);

namespace Zicht\Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zicht\Bundle\PageBundle\Entity\ControllerPageInterface;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Bundle\PageBundle\Model\ViewValidationInterface;

trait PageControllerTrait
{
    public function getPageManager(): PageManager
    {
        if (!property_exists($this, 'pageManager')) {
            throw new \RuntimeException(sprintf('$pageManager property does not exists on %s. You should inject the PageManager into your controller.', get_class($this)));
        }
        if (!($this->pageManager instanceof PageManager)) {
            throw new \RuntimeException(sprintf('$pageManager property does not contain an instance of %s. You should inject the correct PageManager into your controller.', PageManager::class));
        }

        return $this->pageManager;
    }

    public function renderPage(PageInterface $page, array $vars = []): Response
    {
        if (!method_exists($this, 'render')) {
            throw new \RuntimeException(sprintf('render() method does not exists on %s. Your controller should extend %s.', get_class($this), AbstractController::class));
        }

        return $this->render(
            $this->getPageManager()->getTemplate($page),
            $vars + [
                'page' => $page,
                'id' => $page->getId(),
            ]
        );
    }

    protected function getViewActionValidator(): ?ViewValidationInterface
    {
        if (!property_exists($this, 'pageViewValidation')) {
            throw new \RuntimeException(sprintf('pageViewValidation property does not exists on %s. You should inject the ViewValidationInterface into your controller.', get_class($this)));
        }

        if ($this->pageViewValidation instanceof ViewValidationInterface) {
            return $this->pageViewValidation;
        }

        return null;
    }

    private function isViewActionAllowed(PageInterface $page): void
    {
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
    }

    private function shouldForwardControllerPage(PageInterface $page, Request $request): ?Response
    {
        if (!($page instanceof ControllerPageInterface) || $page->getController() === null) {
            return null;
        }

        if (!method_exists($this, 'forward')) {
            throw new \RuntimeException(sprintf('forward() method does not exists on %s. Your controller should extend %s.', get_class($this), AbstractController::class));
        }

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
}
