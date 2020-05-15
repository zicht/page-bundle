<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class AbstractVoter
 *
 * @package Zicht\Bundle\PageBundle\Security\Voter
 */
abstract class AbstractVoter implements VoterInterface
{
    const SUPPORTED_ATTRIBUTES = ['VIEW', 'ACTION_POST_UPDATE', 'ACTION_POST_PERSIST'];

    /**
     * @{inheritDoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, self::SUPPORTED_ATTRIBUTES);
    }

    /**
     * Check if one or more of the given items is not empty
     *
     * @param ...$value
     * @return bool
     */
    protected static function notEmpty(...$value)
    {
        return (bool)count(array_filter($value)) >= 1;
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
}