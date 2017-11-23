<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Aliasing\Strategy;

use Zicht\Bundle\UrlBundle\Aliasing\AliasingStrategy;

/**
 * Class LanguageAwareAliasingStrategy
 *
 * @package Zicht\Bundle\UrlBundle\Url\Aliasing\Strategy
 */
class LanguageAwareAliasingStrategy implements AliasingStrategy
{
    /**
     * @var string
     */
    public $basePath;

    /**
     * @var array
     */
    protected $localesToPrefix;

    /**
     * @var AliasingStrategy
     */
    protected $strategyWrapper;

    /**
     * LanguageAwareAliasingStrategy constructor.
     *
     * @param AliasingStrategy $strategyWrapper
     * @param array $localesToPrefix
     */
    public function __construct(AliasingStrategy $strategyWrapper, $localesToPrefix = array())
    {
        $this->basePath = '/';
        $this->strategyWrapper = $strategyWrapper;
        $this->localesToPrefix = $localesToPrefix;
    }

    /**
     * Generate public alias
     *
     * @param mixed $subject
     * @param string $currentAlias
     * @return string
     */
    public function generatePublicAlias($subject, $currentAlias = '')
    {
        $alias = $this->strategyWrapper->generatePublicAlias($subject, $currentAlias);

        if ($alias !== null && method_exists($subject, 'getLanguage')) {
            if (in_array($subject->getLanguage(), $this->localesToPrefix)) {
                $alias = sprintf('%s%s%s', $this->basePath, $subject->getLanguage(), $alias);
            }
        }

        return $alias;
    }

    /**
     * @param array $localesToPrefix
     * @return void
     */
    public function setLocalesToPrefix($localesToPrefix)
    {
        $this->localesToPrefix = $localesToPrefix;
    }
}
