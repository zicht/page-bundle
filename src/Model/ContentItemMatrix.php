<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
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
 *             ->type(Text::class)
 *             ->type(Embed::class)
 *         ->region('right')
 *             ->type(Text::class)
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
    private $currentRegion;

    /**
     * Contains the matrix
     *
     * @var array<string, class-string<ContentItemInterface>[]>
     */
    private $matrix;

    /**
     * Stubbed constructor.
     */
    public function __construct()
    {
        $this->currentRegion = 'left';
        $this->matrix = [];
    }

    /**
     * Provides fluent interface for building the matrix.
     *
     * @return self
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Select a region to configure.
     *
     * @param string $region
     * @param bool $reset
     * @return $this
     */
    public function region($region, $reset = false)
    {
        $this->currentRegion = $region;
        if ($reset) {
            $this->matrix[$this->currentRegion] = [];
        }

        return $this;
    }

    /**
     * Remove a region
     *
     * @param string $region
     * @return $this
     */
    public function removeRegion($region)
    {
        if (array_key_exists($region, $this->matrix)) {
            unset($this->matrix[$region]);
        }

        return $this;
    }

    /**
     * Remove a type from a region
     *
     * @param class-string<ContentItemInterface> $type
     * @param string $region
     * @return $this
     */
    public function removeTypeFromRegion($type, $region)
    {
        if (array_key_exists($region, $this->matrix)) {
            array_walk(
                $this->matrix[ $region ],
                function ($value, $idx, $matrix) use ($type, $region) {
                    $class = explode('\\', $value);
                    $className = array_pop($class);
                    if ($className === $type) {
                        unset($matrix[ $region ][ $idx ]);
                    }
                },
                $this->matrix
            );
        }

        return $this;
    }


    /**
     * Adds the type to the currently selected region.
     *
     * @param class-string<ContentItemInterface> $className
     * @return $this
     */
    public function type($className)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Class %s is non-existent or could not be loaded',
                    $className
                )
            );
        }
        $this->matrix[$this->currentRegion][] = $className;

        return $this;
    }

    /**
     * Returns the available types for a specified region.
     * If not specified, returns all types configured.
     *
     * @param string|null $region
     * @return class-string<ContentItemInterface>[]
     */
    public function getTypes($region = null)
    {
        if (null === $region) {
            $ret = [];
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
     * @param class-string<ContentItemInterface>|null $type
     * @return string[]
     */
    public function getRegions($type = null)
    {
        if (null === $type) {
            return array_keys($this->matrix);
        }

        $regions = [];
        foreach ($this->matrix as $region => $types) {
            if (in_array($type, $types)) {
                $regions[] = $region;
            }
        }

        return $regions;
    }

    /**
     * @return array<string, class-string<ContentItemInterface>[]>
     */
    public function getMatrix()
    {
        return $this->matrix;
    }
}
