<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Model;

/**
 * Interface to indicate that an object (= parent) has a (child) ContentIntemInterface
 */
interface HasContentItemInterface
{
    public function getContentItem(): ?ContentItemInterface;
}
