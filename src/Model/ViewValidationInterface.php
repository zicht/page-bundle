<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Model;

interface ViewValidationInterface
{
    /**
     * This method will do validation and throw for
     * example a AccessDeniedException if the page
     * is not allowed for view.
     *
     * @return void
     */
    public function validate(PageInterface $page);
}
