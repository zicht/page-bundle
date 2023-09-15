<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Zicht\Bundle\PageBundle\Model\ScheduledContentInterface;

/**
 * Check content against the scheduled dates
 */
class ScheduledContentVoter extends AbstractAdminAwareVoter
{
    /**
     * Decide based on the current date and time what the vote should be. Static so it's strategy can easily be accessed
     * by other components as well, without the actual need for the voter instance.
     *
     * @return int
     */
    public static function decide(ScheduledContentInterface $object, array $attributes = [])
    {
        $now = new \DateTimeImmutable();
        $vote = VoterInterface::ACCESS_ABSTAIN;
        $from = $object->isScheduledFrom();
        $till = $object->isScheduledTill();

        if (!$object->isPublic() || false === self::notEmpty($from, $till)) {
            return $vote;
        }

        switch (true) {
            case is_null($from):
                $vote = $till >= $now ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
                break;
            case is_null($till):
                switch (true) {
                    case $from <= $now:
                        $vote = VoterInterface::ACCESS_GRANTED;
                        break;
                    case $from > $now && self::hasCmsAttribute($attributes):
                        $vote = VoterInterface::ACCESS_GRANTED;
                        break;
                    default:
                        $vote = VoterInterface::ACCESS_DENIED;
                }
                break;
            default:
                switch (true) {
                    case $from <= $now && $till >= $now:
                        $vote = VoterInterface::ACCESS_GRANTED;
                        break;
                    case ($from > $now && $till >= $now) && self::hasCmsAttribute($attributes):
                        $vote = VoterInterface::ACCESS_GRANTED;
                        break;
                    default:
                        $vote = VoterInterface::ACCESS_DENIED;
                }
        }

        return $vote;
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     * @return bool true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return in_array(ScheduledContentInterface::class, class_implements($class));
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        // Abstract class checks if user is admin, if not so it will return VoterInterface::ACCESS_ABSTAIN
        $vote = parent::vote($token, $subject, $attributes);

        /** @var ScheduledContentInterface $subject */
        if ($vote === VoterInterface::ACCESS_ABSTAIN && is_object($subject) && $this->supportsClass(get_class($subject))) {
            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute)) {
                    continue;
                }

                $vote = self::decide($subject, $attributes);
            }
        }

        return $vote;
    }
}
