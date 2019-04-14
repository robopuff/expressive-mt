<?php

namespace AppTest\Middleware;

use App\Action\AbstractRestfulAction;
use App\Middleware\PropagateRestfulActionMiddleware;
use App\Response\ApiProblemResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;

class PropagateRestfulActionMiddlewareTest extends TestCase
{
    /**
     * @var ServerRequestInterface|ObjectProphecy
     */
    private $request;

    /**
     * @var RequestHandlerInterface|ObjectProphecy
     */
    private $handler;

    public function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->request->getMethod()->willReturn('GET');
        $this->request->getAttribute('id')->willReturn(null);

        $this->handler = $this->prophesize(RequestHandlerInterface::class);
    }

    public function methodTestDataProvider(): array
    {
        return [
            ['POST', null, AbstractRestfulAction::METHOD_CREATE],
            ['GET', null, AbstractRestfulAction::METHOD_FETCH_ALL],
            ['GET', 1, AbstractRestfulAction::METHOD_FETCH],
            ['PATCH', 1, AbstractRestfulAction::METHOD_PATCH],
            ['PATCH', null, AbstractRestfulAction::METHOD_PATCH_LIST],
            ['DELETE', 1, AbstractRestfulAction::METHOD_DELETE],
            ['DELETE', null, AbstractRestfulAction::METHOD_DELETE_LIST],
        ];
    }

    /**
     * @dataProvider methodTestDataProvider
     */
    public function testWithValidHTTPMethodProvided($method, $id, $result)
    {
        $this->handler->handle($this->request->reveal())->willReturn(new EmptyResponse())->shouldBeCalled();

        $this->request->getMethod()->willReturn($method);
        $this->request->getAttribute('id')->willReturn($id);

        $this->request->withAttribute(PropagateRestfulActionMiddleware::class, $result)
            ->shouldBeCalled()->will([$this->request, 'reveal']);

        $middleware = new PropagateRestfulActionMiddleware();
        $middleware->process($this->request->reveal(), $this->handler->reveal());
    }

    public function testWithInvalidOrUnsupportedHTTPMethodProvided()
    {
        $this->request->getMethod()->willReturn('RANDOMIZER');

        $middleware = new PropagateRestfulActionMiddleware();
        $response = $middleware->process($this->request->reveal(), $this->handler->reveal());

        $this->assertInstanceOf(ApiProblemResponse::class, $response);
    }
}
