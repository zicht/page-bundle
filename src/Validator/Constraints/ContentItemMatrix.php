<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContentItemMatrix extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'zicht_page.validator.content_item_matrix_validator';
    }
}
