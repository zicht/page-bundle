<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Security\Voter;

use \Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use \Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Checks on 'vote' whether or not the current user is and admin
 *
 * @package Zicht\Bundle\PageBundle\Security\Voter
 */
abstract class AdminAwareVoterAbstract implements VoterInterface
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
     *
     * @return integer either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        /**
         * Admin users should see content no matter the scheduled dates
         * Since you can set the decision strategy to unanimous, you want to grant this explicitly
         */
        if ($this->supportsClass(get_class($object)) && sizeof($token->getRoles())) {
            /** @var \Symfony\Component\Security\Core\Role\Role $role */
            foreach ($token->getRoles() as $role) {
                if (in_array($role->getRole(), array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) {
                    return VoterInterface::ACCESS_GRANTED;
                    break;
                }
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}