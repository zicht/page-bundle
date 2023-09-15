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
    public function supportsClass($class)
    {
        return Page::class === $class || is_subclass_of($class, Page::class);
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        // Abstract class checks if user is admin, if not so it will return VoterInterface::ACCESS_ABSTAIN
        $vote = parent::vote($token, $subject, $attributes);

        if ($vote === VoterInterface::ACCESS_ABSTAIN && is_object($subject) && $this->supportsClass(get_class($subject))) {
            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute)) {
                    continue;
                }

                if ($this->isPublic($subject)) {
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
     * @return bool
     */
    public function isPublic(PageInterface $page)
    {
        return $page->isPublic();
    }
}
