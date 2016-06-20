<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use Zicht\Bundle\PageBundle\Controller\PageController;
use ZichtTest\Bundle\PageBundle\Assets\PageAdapter;

class Page extends PageAdapter {
    public function __construct($id)
    {
        $this->id = $id;
    }


    public function getTitle()
    {
    }


    public function getId()
    {
        return $this->id;
    }
}

class CPage extends Page implements \Zicht\Bundle\PageBundle\Entity\ControllerPageInterface
{
    public function getController()
    {
        return 'Foo:Bar';
    }

    public function getControllerParameters()
    {
        return array('foo' => 'bar');
    }
}

class PageControllerTest extends \PHPUnit_Framework_TestCase
{
    /** @var PageController */
    private $controller;


    public function setUp()
    {
        $this->controller = new PageController();
        $this->pm = $this->getMockBuilder('Zicht\Bundle\PageBundle\Manager\PageManager')->disableOriginalConstructor()->getMock();
        $this->templating =  $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')->getMock();
        $this->request = new \Symfony\Component\HttpFoundation\Request;
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface', array('forward', 'handle'));
        $container = new \Symfony\Component\DependencyInjection\Container();
        $container->set('zicht_page.page_manager', $this->pm);
        $container->set('templating', $this->templating);
        $container->set('request', $this->request);
        $container->set('request_stack', $rs = new RequestStack());
        $rs->push($this->request);

        $container->set('http_kernel', $this->kernel);
        $this->security = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $container->set('security.context', $this->security);

        $this->controller->setContainer($container);
    }

    private function allow()
    {
        $this->security->expects($this->once())->method('isGranted')->will($this->returnValue(true));
    }

    private function deny()
    {
        $this->security->expects($this->once())->method('isGranted')->will($this->returnValue(false));
    }

    function testViewActionFindsPageForView()
    {
        $this->allow();

        $id = rand(1, 100);
        $page = new Page($id);
        $this->pm->expects($this->once())->method('findForView')->with($id)->will($this->returnValue($page));
        $this->pm->expects($this->once())->method('getTemplate')->with($page)->will($this->returnValue('foo.template'));
        $this->templating->expects($this->once())->method('renderResponse')->with('foo.template', array(
            'page' => $page,
            'id' => $id
        ));
        $this->controller->viewAction($id);
    }


    function testViewActionFindsPageForViewAndForwardsIfItIsaControllerPage()
    {
        $this->allow();

        $id = rand(1, 100);
        $page = new CPage($id);
        $this->pm->expects($this->once())->method('findForView')->with($id)->will($this->returnValue($page));
        $this->kernel->expects($this->once())->method('handle');
        $this->controller->viewAction($id);
    }


    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    function testControllerThrowsAccessDeniedExceptionIfNotAllowed()
    {
        $this->deny();
        $id = rand(1, 100);
        $page = new CPage($id);
        $this->pm->expects($this->once())->method('findForView')->with($id)->will($this->returnValue($page));
        $this->controller->viewAction($id);
    }
}