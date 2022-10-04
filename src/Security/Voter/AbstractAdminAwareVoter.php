<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Checks on 'vote' whether or not the current user is and admin
 */
abstract class AbstractAdminAwareVoter extends AbstractVoter
{
    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param object $object The object to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // ignore checks for switch user
        if (in_array('ROLE_PREVIOUS_ADMIN', $attributes)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        /*
         * Admin users should see content no matter the scheduled dates
         * Since you can set the decision strategy to unanimous, you want to grant this explicitly
         */
        if (!is_null($object) && $this->supportsClass(get_class($object)) && count($token->getRoleNames()) > 0) {
            foreach ($token->getRoleNames() as $role) {
                if (in_array($role, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
