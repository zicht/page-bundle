<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ContentItemMatrix
 *
 * @package Zicht\Bundle\PageBundle\Validator\Constraints
 */
class ContentItemMatrix extends Constraint
{

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * Returns service name
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'zicht_page.validator.content_item_matrix_validator';
    }
}
