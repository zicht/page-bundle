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
class PageVoter extends AbstractAdminAwareVoter
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
        // Abstract class checks if user is admin, if not so it will return VoterInterface::ACCESS_ABSTAIN
        $vote = parent::vote($token, $object, $attributes);

        if ($vote === VoterInterface::ACCESS_ABSTAIN && !is_null($object) && $this->supportsClass(get_class($object))) {
            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute)) {
                    continue;
                }

                if ($this->isPublic($object)) {
                    $vote = VoterInterface::ACCESS_GRANTED;
                    break;
                }
            }
        }

        return $vote;
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
