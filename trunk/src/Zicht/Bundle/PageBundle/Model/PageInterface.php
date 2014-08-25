<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Model;

/**
 * Common interfaces for the page model.
 */
interface PageInterface extends ContentItemContainer
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


    /**
     * A page must always have an id
     *
     * @return mixed
     */
    public function getId();


    /**
     * A page must be able to display it's own type as a human readable string.
     *
     * @return string
     */
    public function getDisplayType();


    /**
     * @return bool
     */
    public function isPublic();
}