<?php
/**
 * @author Philip Bergman <philip@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Security;

use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Bundle\PageBundle\Model\ViewValidationInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class PageViewValidation
 *
 * @package Zicht\Bundle\PageBundle\Security
 */
class PageViewValidation implements ViewValidationInterface
{
    /** @var null|AuthorizationCheckerInterface */
    protected $auth;

    /**
     * PageViewValidation constructor.
     *
     * @param AuthorizationCheckerInterface|null $auth
     */
    public function __construct(AuthorizationCheckerInterface $auth = null)
    {
        $this->auth = $auth;
    }

    /**
     * Similar to PageController it will return true when auth === null
     *
     * @param PageInterface $page
     * @return bool
     */
    protected function isAllowed(PageInterface $page)
    {
        if (null !== $this->auth) {
            return $this->auth->isGranted('VIEW', $page);
        }
        return true;
    }


    /**
     * @{inheritDoc}
     */
    public function validate(PageInterface $page)
    {
        if (false === $this->isAllowed($page)) {
            throw new AccessDeniedException("Page {$page->getId()} is not accessible to the current user");
        }
    }
}
