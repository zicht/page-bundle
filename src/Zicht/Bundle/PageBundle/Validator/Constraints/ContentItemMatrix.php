<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContentItemMatrix extends Constraint
{
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return 'zicht_page.validator.content_item_matrix_validator';
    }
}