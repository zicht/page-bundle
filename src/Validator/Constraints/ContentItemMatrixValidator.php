<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zicht\Bundle\PageBundle\Entity\ContentItem;
use Zicht\Bundle\PageBundle\Model\ContentItemContainer;
use Zicht\Util\Str;

class ContentItemMatrixValidator extends ConstraintValidator
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param ContentItemContainer $value
     */
    public function validate($value, Constraint $constraint)
    {
        $matrix = $value->getContentItemMatrix();

        if (null !== $matrix) {
            /**
             * @var int $i
             * @var ContentItem $contentItem
             */
            foreach ($value->getContentItems() as $i => $contentItem) {
                $type = get_class($contentItem);
                $typeName = $this->translator->trans('content_item.type.' . strtolower(str_replace(' ', '_', Str::humanize($contentItem->getType()))), [], 'admin');

                if ((string)$contentItem->getRegion() === '') {
                    $this->context->buildViolation(
                        'content_item.invalid.region_empty',
                        ['%type%' => $typeName]
                    )->atPath(sprintf('contentItems[%d]', $i))->addViolation();
                } elseif (!in_array($contentItem->getRegion(), $matrix->getRegions($type))) {
                    $this->context->buildViolation(
                        'content_item.invalid.region_type_combination',
                        ['%region%' => $contentItem->getRegion(), '%type%' => $typeName]
                    )->atPath(sprintf('contentItems[%d]', $i))->addViolation();
                }
            }
        }
    }
}
