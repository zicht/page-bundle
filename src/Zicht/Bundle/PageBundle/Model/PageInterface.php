<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Model;

/**
 * Common interfaces for the page model.
 */
interface PageInterface
{
    /**
     * Returns the template name to be used by the controller.
     *
     * @return string
     */
    public function getTemplateName();


    /**
     * A page must always have a title.
     *
     * @return mixed
     */
    public function getTitle();
}