<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Validator\Constraints;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContentItemMatrixValidator extends ConstraintValidator
{
    function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }


    public function validate($value, Constraint $constraint)
    {
        $matrix = $value->getContentItemMatrix();

        if (null !== $matrix) {
            foreach ($value->getContentItems() as $i => $contentItem) {
                $type = get_class($contentItem);

                if (!in_array($contentItem->getRegion(), $matrix->getRegions($type))) {
                    $this->context->addViolationAt(
                        'contentItems[' . $i . ']',
                        $this->translator->trans(
                            "content_item.invalid.region.type.combination",
                            array(
                                '@region' => $contentItem->getRegion(),
                                '@type' => $type
                            ),
                            'validators'
                        )
                    );
                }
            }
        }
    }
}