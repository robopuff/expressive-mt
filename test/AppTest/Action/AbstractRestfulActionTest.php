<?php

namespace AppTest\Action;

use App\Action\AbstractRestfulAction;
use App\ApiProblem;
use App\Item\Entity;
use App\Middleware\PropagateRestfulActionMiddleware;
use App\Response\ApiProblemResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

class AbstractRestfulActionTest extends TestCase
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
        $this->handler = $this->prophesize(RequestHandlerInterface::class);

        $this->request->getAttribute('id', false)->willReturn(false);
        $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn('fetchAll');
        $this->request->getAttribute('ResponseType', JsonResponse::class)->willReturn(JsonResponse::class);
        $this->request->getParsedBody()->willReturn([]);
        $this->request->getQueryParams()->willReturn([]);
    }

    public function testInvokeWithoutMethod()
    {
        $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn(null);

        /** @var AbstractRestfulAction $stub */
        $stub = $this->getMockForAbstractClass(AbstractRestfulAction::class);

        $response = $stub->process($this->request->reveal(), $this->handler->reveal());
        $this->assertInstanceOf(ApiProblemResponse::class, $response);
    }

    public function testApiProblemResponse()
    {
        $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn('fetchAll');

        /** @var AbstractRestfulAction $stub */
        $stub = new class extends AbstractRestfulAction {
            public function fetchAll($params = []){ return new ApiProblem(411, 'Test problem'); }
        };

        $result = $stub->process($this->request->reveal(), $this->handler->reveal());

        $this->assertInstanceOf(ApiProblemResponse::class, $result);
        $this->assertEquals(411, $result->getStatusCode());
    }

    public function testPaginatorResponse()
    {
        $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn('fetchAll');
        $this->request->getQueryParams()->willReturn(['limit'=>32]);

        /** @var AbstractRestfulAction $stub */
        $stub = new class extends AbstractRestfulAction {
            public function fetchAll($params = [])
            {
                return new Paginator(new ArrayAdapter([
                    new Entity()
                ]));
            }
        };

        /** @var JsonResponse $result */
        $result = $stub->process($this->request->reveal(), $this->handler->reveal());

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());

        $content = $result->getBody()->getContents();

        $this->assertNotEmpty($content);
        $this->assertContains('"_embedded":[{"id":null,"unit_price":null,"qty":null}],', $content);
        $this->assertContains('"page":1,', $content);
        $this->assertContains('"page_size":32,', $content);
    }

    public function testEntityResponse()
    {
        $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn('fetch');
        $this->request->getQueryParams()->willReturn([]);

        $entity = new Entity();
        /** @var AbstractRestfulAction $stub */
        $stub = new class extends AbstractRestfulAction {
            public function fetch($params = [])
            {
                return new Entity();
            }
        };

        /** @var HtmlResponse $result */
        $result = $stub->process($this->request->reveal(), $this->handler->reveal());

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());

        $content = $result->getBody()->getContents();
        $this->assertNotEmpty($content);
        $this->assertEquals(json_encode($entity->getArrayCopy()), $content);
    }

    public function testScalarResponse()
    {
        $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn('fetchAll');
        $this->request->getQueryParams()->willReturn([]);

        /** @var AbstractRestfulAction $stub */
        $stub = new class extends AbstractRestfulAction {
            public function fetchAll($params = []){ return new HtmlResponse('--response--', 201); }
        };

        /** @var HtmlResponse $result */
        $result = $stub->process($this->request->reveal(), $this->handler->reveal());

        $this->assertInstanceOf(HtmlResponse::class, $result);
        $this->assertEquals(201, $result->getStatusCode());

        $content = $result->getBody()->getContents();

        $this->assertNotEmpty($content);
        $this->assertEquals('--response--', $content);
    }

    public function testResponseInterfaceResponse()
    {
        /** @var AbstractRestfulAction $stub */
        $stub = new class extends AbstractRestfulAction {
            public function fetchAll($params = []){ return ['array', 'of', 'strings']; }
        };

        /** @var JsonResponse $result */
        $result = $stub->process($this->request->reveal(), $this->handler->reveal());

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());

        $content = $result->getBody()->getContents();

        $this->assertNotEmpty($content);
        $this->assertEquals('["array","of","strings"]', $content);
    }

    public function testDispatchDefaultNotExist()
    {
        $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn('NONEXISTENT');

        /** @var AbstractRestfulAction $stub */
        $stub = $this->getMockForAbstractClass(AbstractRestfulAction::class);
        $result = $stub->process($this->request->reveal(), $this->handler->reveal());

        $this->assertInstanceOf(ApiProblemResponse::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testDispatchSwitchCase()
    {
        $this->request->getParsedBody()->willReturn(['RESPONSE']);
        $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn('create');

        /** @var AbstractRestfulAction $stub */
        $stub = new class extends AbstractRestfulAction {
            public function create($data){ return 'createResponse';}
            public function delete($id){ return 'deleteResponse'; }
            public function deleteList($data){ return 'deleteListResponse'; }
            public function fetch($id){ return 'fetchResponse'; }
            public function fetchAll($params = []){ return 'fetchAllResponse'; }
            public function patch($id, $data){ return 'patchResponse'; }
            public function patchList($data){ return 'patchListResponse'; }
        };

        foreach (['create', 'delete', 'deleteList',
                     'fetch', 'fetchAll', 'patch',
                     'patchList'] as $method) {
            $this->request->getAttribute(PropagateRestfulActionMiddleware::class)->willReturn($method);
            $result = $stub->process($this->request->reveal(), $this->handler->reveal());

            $this->assertInstanceOf(JsonResponse::class, $result);
            $this->assertEquals(sprintf('"%sResponse"', $method), $result->getBody()->getContents());
        }
    }

    public function testGetHydrator()
    {
        /** @var AbstractRestfulAction $stub */
        $stub = $this->getMockForAbstractClass(AbstractRestfulAction::class);
        $this->assertInstanceOf(HydratorInterface::class, $stub->getHydrator());
    }
}
