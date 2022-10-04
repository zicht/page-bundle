<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Validator\Constraints;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zicht\Bundle\PageBundle\Entity\ContentItem;

class ContentItemMatrixValidator extends ConstraintValidator
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var \Zicht\Bundle\PageBundle\Model\ContentItemMatrix $matrix */
        $matrix = $value->getContentItemMatrix();

        if (null !== $matrix) {
            /**
             * @var int $i
             * @var ContentItem $contentItem
             */
            foreach ($value->getContentItems() as $i => $contentItem) {
                $type = get_class($contentItem);

                if (!in_array($contentItem->getRegion(), $matrix->getRegions($type))) {
                    $message = $this->translator->trans('content_item.invalid.region_type_combination', ['%region%' => $contentItem->getRegion(), '%type%' => $type], 'validators');
                    $this->context->buildViolation($message)
                        ->atPath(sprintf('contentItems[%d]', $i))
                        ->addViolation();
                }
            }
        }
    }
}
