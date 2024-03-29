<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Model;

/**
 * Interface ScheduledContentInterface
 *
 * Implement to define when content is visible to the public and when not.
 * Use in combination with the ScheduledContentVoter.
 */
interface ScheduledContentInterface extends PublicInterface
{
    /**
     * @return \DateTime|null
     */
    public function isScheduledFrom();

    /**
     * @return \DateTime|null
     */
    public function isScheduledTill();
}
