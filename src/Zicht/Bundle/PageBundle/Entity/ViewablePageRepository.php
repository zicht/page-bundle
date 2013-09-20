<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
  */

namespace Zicht\Bundle\PageBundle\Entity;

interface ViewablePageRepository
{
    /**
     * @return \Zicht\Bundle\PageBundle\Model\PageInterface
     * */
    public function findForView($id);
}