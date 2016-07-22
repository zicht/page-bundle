<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Zicht\Bundle\PageBundle\Model\ScheduledContentInterface;

/**
 * Check content against the scheduled dates
 *
 * @package Zicht\Bundle\PageBundle\Security\Voter
 */
class ScheduledContentVoter extends AdminAwareVoterAbstract
{
    /**
     * The 'view' attribute
     */
    const VIEW = 'VIEW';

    /**
     * @{inheritDoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(self::VIEW));
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return Boolean true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return in_array('Zicht\Bundle\PageBundle\Model\ScheduledContentInterface', class_implements($class));
    }

    /**
     * @{inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // Abstract class checks if user is admin, if not so it will return VoterInterface::ACCESS_ABSTAIN
        $vote = parent::vote($token, $object, $attributes);

        /** @var ScheduledContentInterface $object */
        if ($vote === VoterInterface::ACCESS_ABSTAIN && $this->supportsClass(get_class($object))) {
            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute)) {
                    continue;
                }

                $scheduledFrom = $object->isScheduledFrom();
                $scheduledTill = $object->isScheduledTill();

                if (null === $scheduledFrom && null === $scheduledTill) {
                    $vote = VoterInterface::ACCESS_ABSTAIN;
                } else {
                    $currentDateTime = new \DateTime();
                    if (null !== $scheduledFrom && null !== $scheduledTill) {
                        // Check between datetime objects
                        $vote = $scheduledFrom <= $currentDateTime && $scheduledTill >= $currentDateTime ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
                    } elseif (null !== $scheduledFrom && null === $scheduledTill) {
                        // Check only "from "date
                        $vote = $scheduledFrom <= $currentDateTime ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
                    } elseif (null == $scheduledFrom && null !== $scheduledTill) {
                        // Check only "till" date
                        $vote = $scheduledTill >= $currentDateTime ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
                    }
                }
            }
        }

        return $vote;
    }
}