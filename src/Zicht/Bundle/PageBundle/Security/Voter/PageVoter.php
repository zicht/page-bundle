<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
        $supportedClass = 'Zicht\Bundle\PageBundle\Entity\Page';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    /**
     * @{inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($object))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if ($object instanceof PageInterface && $this->isPublic($object)) {
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
