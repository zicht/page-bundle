<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
namespace Zicht\Bundle\PageBundle\Model;


/**
 * Helper class to configure the contentitem region / type matrix.
 *
 * Usage:
 *
 * function getContentItemMatrix() {
 *     return ContentItemMatrix::create()
 *         ->region('left')
 *             ->type('My\ContentItem\Type')
 *             ->type('My\Other\ContentItem\Type')
 *         ->region('right')
 *             ->type('Yet\Another\One')
 *     ;
 * }
 * return
 */
final class ContentItemMatrix
{
    /**
     * Points to the current region being configured.
     *
     * @var string
     */
    private $currentRegion = 'left';

    /**
     * Contains the matrix
     *
     * @var array
     */
    private $matrix = array();

    /**
     * Namespace prefix for the types that are registered.
     *
     * @var string
     */
    private $namespacePrefix = '';

    /**
     * Provides fluent interface for building the matrix.
     *
     * @param string $namespacePrefix
     * @return ContentItemMatrix
     */
    public static function create($namespacePrefix = null)
    {
        $ret = new self();
        if (null !== $namespacePrefix) {
            $ret->ns($namespacePrefix);
        }
        return $ret;
    }


    /**
     * Stubbed constructor.
     */
    public function __construct()
    {
    }


    /**
     * Select a region to configure.
     *
     * @param string $region
     * @param bool $reset
     * @return ContentItemMatrix
     */
    public function region($region, $reset = false)
    {
        $this->currentRegion = $region;
        if ($reset) {
            $this->matrix[$this->currentRegion] = array();
        }
        return $this;
    }


    /**
     * Adds the type to the currently selected region.
     *
     * @param string $type
     * @return ContentItemMatrix
     */
    public function type($type)
    {
        $this->matrix[$this->currentRegion][]= $this->namespacePrefix . $type;
        return $this;
    }


    /**
     * Prefix all registered classes with this namespace
     *
     * @param string $namespace
     * @return self
     */
    public function ns($namespace)
    {
        if ('\\' !== $namespace{strlen($namespace) -1}) {
            $namespace .= '\\';
        }
        $this->namespacePrefix = $namespace;
        return $this;
    }


    /**
     * Returns the available types for a specified region.
     * If not specified, returns all types configured.
     *
     * @param string $region
     * @return array
     */
    public function getTypes($region = null)
    {
        if (null === $region) {
            $ret = array();
            foreach ($this->matrix as $types) {
                $ret = array_merge($ret, $types);
            }
            return array_values(array_unique($ret));
        }
        return $this->matrix[$region];
    }


    /**
     * Returns the available regions for a specified type.
     * If not specified, all regions are returned.
     *
     * @param null $type
     * @return array|int|string
     */
    public function getRegions($type = null)
    {
        if (null === $type) {
            return array_keys($this->matrix);
        }
        $ret = array();
        foreach ($this->matrix as $region => $types) {
            if (in_array($type, $types)) {
                $ret[]= $region;
            }
        }
        return $ret;
    }
}