<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
  */

namespace Zicht\Bundle\PageBundle\Entity;

/**
 * Interface for repositories that have extra logic for finding a page 'for view'
 */
interface ViewablePageRepository
{
    /**
     * Returns a page requested for 'view'
     *
     * @param mixed $id
     * @return \Zicht\Bundle\PageBundle\Model\PageInterface
     */
    public function findForView($id);
}