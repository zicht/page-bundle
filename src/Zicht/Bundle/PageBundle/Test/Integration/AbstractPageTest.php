<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Test\Integration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Zicht\Bundle\PageBundle\Controller\PageController;
use Zicht\Bundle\PageBundle\Manager\PageManager;

/**
 * Class AbstractPageTest
 *
 * @package Zicht\Bundle\PageBundle\Test\Integration
 */
abstract class AbstractPageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \AppKernel
     */
    public $kernel;

    /**
     * @{inheritDoc}
     */
    public function setUp()
    {
        $this->kernel = new \AppKernel();
        $this->kernel->boot();

        // we need to synthesize the request, because it could be used by the controller or it's templates.
        $request = new Request();

        $this->kernel->getContainer()->get('event_dispatcher')->dispatch(
            KernelEvents::REQUEST,
            new GetResponseEvent($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST)
        );
        $this->kernel->getContainer()->enterScope('request');
        $this->kernel->getContainer()->set('request', $request, 'request');
    }

    /**
     * Creates page manager
     *
     * @return PageManager
     */
    protected function createPageManager()
    {
        $kernel = new \AppKernel();
        $kernel->boot();

        $pageManager = $kernel->getContainer()->get('zicht_page.page_manager');
        return $pageManager;
    }

    /**
     * Creates page controller
     *
     * @return PageController
     */
    protected function createPageController()
    {
        $pageController = new PageController();
        $pageController->setContainer($this->kernel->getContainer());
        return $pageController;
    }
}
