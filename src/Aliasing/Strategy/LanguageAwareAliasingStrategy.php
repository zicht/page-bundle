<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Aliasing\Strategy;

use Zicht\Bundle\UrlBundle\Aliasing\AliasingStrategy;

class LanguageAwareAliasingStrategy implements AliasingStrategy
{
    /** @var string */
    public $basePath;

    /** @var array */
    protected $localesToPrefix;

    /** @var AliasingStrategy */
    protected $strategyWrapper;

    /**
     * @param array $localesToPrefix
     */
    public function __construct(AliasingStrategy $strategyWrapper, $localesToPrefix = [])
    {
        $this->basePath = '/';
        $this->strategyWrapper = $strategyWrapper;
        $this->localesToPrefix = $localesToPrefix;
    }

    /**
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
     */
    public function setLocalesToPrefix($localesToPrefix): void
    {
        $this->localesToPrefix = $localesToPrefix;
    }
}
