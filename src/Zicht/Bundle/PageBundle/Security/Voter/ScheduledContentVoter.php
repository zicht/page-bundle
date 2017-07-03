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
     * Decide based on the current date and time what the vote should be. Static so it's strategy can easily be accessed
     * by other components as well, without the actual need for the voter instance.
     *
     * @param ScheduledContentInterface $object
     * @param array $attributes
     * @return int
     */
    public static function decide(ScheduledContentInterface $object, array $attributes = [])
    {
        $valid  = false;
        $valid |= (null === ($from = $object->isScheduledFrom()));
        $valid |= (null === ($till = $object->isScheduledTill()));
        $now = new \DateTimeImmutable();
        $vote = VoterInterface::ACCESS_ABSTAIN;

        if (!$object->isPublic() || false === (bool)$valid) {
            return $vote;
        }

        switch (true) {
            case is_null($from):
                $vote = $till >= $now ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
                break;
            case is_null($till):
                switch (true) {
                    case ($from <= $now):
                        $vote = VoterInterface::ACCESS_GRANTED;
                        break;
                    case ($from > $now && self::hasCmsAttribute($attributes)):
                        $vote = VoterInterface::ACCESS_GRANTED;
                        break;
                    default:
                        $vote = VoterInterface::ACCESS_DENIED;
                }
                break;
            default:
                switch (true) {
                    case ($from <= $now && $till >= $now):
                        $vote = VoterInterface::ACCESS_GRANTED;
                        break;
                    case (($from > $now && $till >= $now) && self::hasCmsAttribute($attributes)):
                        $vote = VoterInterface::ACCESS_GRANTED;
                        break;
                    default:
                        $vote = VoterInterface::ACCESS_DENIED;
                }
        }

        return $vote;
    }

    /**
     * Check if the given attributes contain cms roles/attributes
     *
     * @param array $attributes
     * @return bool
     */
    protected static function hasCmsAttribute(array $attributes = [])
    {
        return (in_array('ACTION_POST_UPDATE', $attributes) || in_array('ACTION_POST_PERSIST', $attributes));
    }

    /**
     * @{inheritDoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array('VIEW', 'ACTION_POST_UPDATE', 'ACTION_POST_PERSIST'));
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

                $vote = self::decide($object, $attributes);
            }
        }

        return $vote;
    }
}
