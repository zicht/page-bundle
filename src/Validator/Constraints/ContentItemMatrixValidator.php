<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Validator\Constraints;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use Zicht\Bundle\PageBundle\Model\HasContentItemInterface;

class ContentItemMatrixValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     *
     * @param ContentItemContainer $value
     */
    public function validate($value, Constraint $constraint)
    {
        $matrix = $value->getContentItemMatrix();

        if (null !== $matrix) {
            foreach ($value->getContentItems() as $i => $contentItem) {
                $type = get_class($contentItem);

                if ($contentItem instanceof HasContentItemInterface && $contentItem->getContentItem()) {
                    $subType = get_class($contentItem->getContentItem());
                    if (in_array($contentItem->getContentItem()->getRegion(), $matrix->getRegions($subType))) {
                        continue;
                    }

                    $message = $this->translator->trans('content_item.invalid.region_type_combination', ['%region%' => $contentItem->getRegion(), '%type%' => sprintf('%s (%s)', $type, $subType)], 'validators');
                } elseif (in_array($contentItem->getRegion(), $matrix->getRegions($type))) {
                    continue;
                } else {
                    $message = $this->translator->trans('content_item.invalid.region_type_combination', ['%region%' => $contentItem->getRegion(), '%type%' => $type], 'validators');
                }

                $this->context->buildViolation($message)
                    ->atPath(sprintf('contentItems[%d]', $i))
                    ->addViolation();
            }
        }
    }
}
