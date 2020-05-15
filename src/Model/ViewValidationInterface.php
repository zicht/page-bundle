<?php
/**
 * @author Philip Bergman <philip@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Model;

/**
 * Interface ViewValidationInterface
 *
 * @package Zicht\Bundle\PageBundle\Model
 */
interface ViewValidationInterface
{
    /**
     * This method will do validation and throw for
     * example a AccessDeniedException if the page
     * is not allowed for view.
     *
     * @param PageInterface $page
     * @return void
     */
    public function validate(PageInterface $page);
}