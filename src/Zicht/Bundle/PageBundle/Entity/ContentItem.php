<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Entity;

use Zicht\Util\Str;
use Zicht\Bundle\PageBundle\Model\ContentItemInterface;

/**
 * Base class for ContentItem entities.
 */
abstract class ContentItem implements ContentItemInterface
{
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
     * @param ContentItemInterface $from
     * @param ContentItemInterface $to
     * @return ContentItemInterface
     */
    public static function convert(ContentItemInterface $from, ContentItemInterface $to)
    {
        $reflectionFrom = new \ReflectionClass($from);
        $reflectionTo = new \ReflectionClass($to);

        foreach ($reflectionFrom->getProperties() as $property) {
            $property->setAccessible(true);
            $method = 'set' . ucfirst($property->getName());
            if ($reflectionTo->hasMethod($method)) {
                $to->$method($property->getValue($from));
            }
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


    protected $convertToType = null;

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
     * Set the type to convert to.
     *
     * @param string $type
     * @return void
     */
    public function setConvertToType($type)
    {
        $this->convertToType = $type;
    }

    /**
     * If needed, you can add a custom template name to the content item
     *
     * @return null|string
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
