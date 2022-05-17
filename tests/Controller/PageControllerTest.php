<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

class PageControllerTest extends TestCase
{
    /** @var PageController */
    private $controller;

    public function setUp(): void
    {
        $this->controller = new PageController();
        $this->pm = $this->getMockBuilder('Zicht\Bundle\PageBundle\Manager\PageManager')->disableOriginalConstructor()->getMock();
        $this->twig =  $this->getMockBuilder('Twig\Environment')->disableOriginalConstructor()->getMock();
        $this->viewValidator =  $this->getMockBuilder('Zicht\Bundle\PageBundle\Security\PageViewValidation')->getMock();

        $this->viewValidator->method('validate')->willReturn(null);

        $request = $this->createMock(Request::class);
        $request->method('duplicate')->willReturn($request);

        $query = $this->createMock(ParameterBag::class);

        $query->method('all')->willReturn([]);
        $request->query = $query;

        $attributes = $this->createMock(ParameterBag::class);
        $attributes->method('get')->willReturn('xyz');

        $request->attributes = $attributes;

        $this->request = $request;

        $this->kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->setMethods(array('forward', 'handle'))->getMock();
        $container = new \Symfony\Component\DependencyInjection\Container();
        $container->set('zicht_page.page_manager', $this->pm);
        $container->set('twig', $this->twig);
        $container->set('request', $this->request);
        $container->set('zicht_page.controller.view_validator', $this->viewValidator);

        $rs = $this->createMock(RequestStack::class);
        $rs->method('getCurrentRequest')->willReturn($request);

        $container->set('request_stack', $rs);

        $container->set('http_kernel', $this->kernel);
        $this->security = $this->createMock('Symfony\Component\Security\Core\Authorization\AuthorizationChecker');
        $container->set('security.authorization_checker', $this->security);

        $this->controller->setContainer($container);
    }

    private function allow()
    {
        $this->viewValidator->expects($this->once())->method('validate')->will($this->returnValue(true));
    }

    private function deny()
    {
        $this->viewValidator->expects($this->once())->method('validate')->will($this->throwException(new AccessDeniedException()));
    }

    function testViewActionFindsPageForView()
    {
        $this->allow();

        $id = rand(1, 100);
        $page = new Page($id);
        $this->pm->expects($this->once())->method('findForView')->with($id)->will($this->returnValue($page));
        $this->pm->expects($this->once())->method('getTemplate')->with($page)->will($this->returnValue('foo.template'));
        $this->twig->expects($this->once())->method('render')->with('foo.template', array(
            'page' => $page,
            'id' => $id
        ));

        $this->controller->viewAction($this->request, $id);
    }


    function testViewActionFindsPageForViewAndForwardsIfItIsaControllerPage()
    {
        $this->allow();

        $id = rand(1, 100);
        $page = new CPage($id);
        $this->pm->expects($this->once())->method('findForView')->with($id)->will($this->returnValue($page));
        $this->kernel->expects($this->once())->method('handle')->willReturn($this->createMock('Symfony\Component\HttpFoundation\Response'));

        $this->controller->viewAction($this->request, $id);
    }


    function testControllerThrowsAccessDeniedExceptionIfNotAllowed()
    {
        $this->expectException('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException');
        $this->deny();
        $id = rand(1, 100);
        $page = new CPage($id);
        $this->pm->expects($this->once())->method('findForView')->with($id)->will($this->returnValue($page));

        $this->controller->viewAction($this->request, $id);
    }
}
