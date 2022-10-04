<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Security;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Bundle\PageBundle\Model\ViewValidationInterface;

class PageViewValidation implements ViewValidationInterface
{
    /** @var AuthorizationCheckerInterface|null */
    protected $auth;

    public function __construct(AuthorizationCheckerInterface $auth = null)
    {
        $this->auth = $auth;
    }

    /**
     * Similar to PageController it will return true when auth === null
     *
     * @return bool
     */
    protected function isAllowed(PageInterface $page)
    {
        if (null !== $this->auth) {
            return $this->auth->isGranted('VIEW', $page);
        }
        return true;
    }

    public function validate(PageInterface $page): void
    {
        if (false === $this->isAllowed($page)) {
            throw new AccessDeniedException("Page {$page->getId()} is not accessible to the current user");
        }
    }
}
