<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Zicht\Bundle\PageBundle\Entity\Page;
use Zicht\Bundle\PageBundle\Model\PageInterface;

/**
 * Votes for pages to be public for anyone.
 */
class PageVoter extends AbstractVoter
{
    /**
     * @{inheritDoc}
     */
    public function supportsClass($class)
    {
        return Page::class === $class || is_subclass_of($class, Page::class);
    }

    /**
     * @{inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!is_null($object) && !$this->supportsClass(get_class($object))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if ($this->isPublic($object)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }


    /**
     * Checks whether the given page is public. If not, it abstains from voting
     *
     * @param PageInterface $page
     * @return bool
     */
    public function isPublic(PageInterface $page)
    {
        return $page->isPublic();
    }
}
