<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Entity;

/**
 * Interface for a page that provides it's own controller.
 */
interface ControllerPageInterface
{
    /**
     * Returns a controller reference (string) which is responsible for rendering the page.
     *
     * @return mixed
     */
    public function getController();


    /**
     * Returns an array of parameters to pass into the controller.
     *
     * @return array
     */
    public function getControllerParameters();
}
