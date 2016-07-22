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
     * This is "type cast", PHP style. Copies all properties from the source to the target, if the target has the
     * specified properties. Of course, this only works for protected properties.
     *
     * @param self $from
     * @param self $to
     * @return mixed
     */
    public static function convert($from, $to)
    {
        foreach (get_object_vars($from) as $property => $value) {
            if (property_exists($to, $property)) {
                $to->{$property} = $value;
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
