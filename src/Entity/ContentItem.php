<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Entity;

use Zicht\Bundle\PageBundle\Model\ContentItemInterface;
use Zicht\Util\Str;

/**
 * Base class for ContentItem entities.
 *
 * @method setPage(?Page $page)
 */
abstract class ContentItem implements ContentItemInterface
{
    protected $convertToType = null;

    /**
     * Returns a (dash notated) short type that can be used in databases, css classes, etc.
     *
     * @param string $infix
     * @return string
     */
    public function getShortType($infix = '-')
    {
        $shortType = Str::infix(lcfirst(Str::classname(Str::rstrip($this->getType(), 'ContentItem'))), $infix);

        if ($template = $this->getTemplateName()) {
            $shortType .= $infix . $template;
        }

        return $shortType;
    }

    /**
     * Copies all properties from the source to the target, if the target has the specified properties.
     *
     * @return ContentItemInterface
     */
    public static function convert(ContentItemInterface $from, ContentItemInterface $to)
    {
        $properties = [];
        try {
            $reflectionFrom = new \ReflectionClass($from);
            $reflectionTo = new \ReflectionClass($to);

            do {
                $convertToType = [];
                /* @var $p \ReflectionProperty */
                foreach ($reflectionFrom->getProperties() as $property) {
                    $property->setAccessible(true);
                    $method = 'set' . ucfirst($property->getName());

                    if (!$reflectionTo->hasMethod($method)) {
                        continue;
                    }
                    $to->$method($property->getValue($from));
                    $convertToType[$method] = $to->$method($property->getValue($from));
                }
                $properties = array_merge($convertToType, $properties);
            } while ($reflectionFrom = $reflectionFrom->getParentClass());
        } catch (\ReflectionException $e) {
            // error not needed to shown when there is no parent
        }

        return $to;
    }

    /**
     * Returns the type of the class.
     *
     * @return string
     */
    final public function getType()
    {
        return get_class($this);
    }

    /**
     * Return string
     *
     * @return string
     */
    final public function getConvertToType()
    {
        if (null !== $this->convertToType) {
            return $this->convertToType;
        }
        return $this->getType();
    }

    /**
     * @param string $type
     */
    public function setConvertToType($type): void
    {
        $this->convertToType = $type;
    }

    /**
     * If needed, you can add a custom template name to the content item
     *
     * @return string|null
     */
    public function getTemplateName()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getInternalName()
    {
        return (string)$this;
    }

    /**
     * get region of content item, for BC just
     * setting as empty place holder.
     *
     * @return string
     */
    public function getRegion()
    {
    }
}
