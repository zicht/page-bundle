<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Model;

/**
 * Interface PublicInterface
 *  *
 * @package Zicht\Bundle\PageBundle\Model
 */
interface PublicInterface
{
    /**
     * @return bool
     */
    public function isPublic();
}