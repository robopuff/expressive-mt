<?php

namespace AppTest;

use App\ApiProblem;
use PHPUnit\Framework\TestCase;

class ApiProblemTest extends TestCase
{
    public function statusCodes()
    {
        return [
            '200' => [200],
            '201' => [201],
            '300' => [300],
            '301' => [301],
            '302' => [302],
            '400' => [400],
            '401' => [401],
            '404' => [404],
            '500' => [500],
        ];
    }

    public function invalidStatusCodes()
    {
        return [
            '-1'  => [-1],
            '0'   => [0],
            '1'   => [1],
            '7'   => [7],
            '14'  => [14],
            '501' => [600],
            '800' => [800],
            'string' => ['string']
        ];
    }

    /**
     * @dataProvider statusCodes
     * @param $status
     */
    public function testStatusIsUsedVerbatim($status)
    {
        $apiProblem = new ApiProblem($status, 'foo');
        $payload = $apiProblem->toArray();
        $this->assertArrayHasKey('status', $payload);
        $this->assertEquals($status, $payload['status']);
    }

    /**
     * @dataProvider invalidStatusCodes
     * @param $status
     */
    public function testErrorCodeHigherThan500($status)
    {
        $apiProblem = new ApiProblem($status, 'message');
        $this->assertEquals(500, $apiProblem->getStatusCode());
    }

    public function testSettingType()
    {
        $type = bin2hex(random_bytes(2));
        $apiProblem = new ApiProblem(800, 'foo', $type);

        $this->assertArrayHasKey('type', $apiProblem->toArray());
        $this->assertEquals($type, $apiProblem->toArray()['type']);
    }

    public function testAdditionalDetails()
    {
        $details = ['randomAdditional' => random_bytes(2), 'random' => random_bytes(2)];
        $apiProblem = new ApiProblem(500, 'message', null, null, $details);

        $payload = $apiProblem->toArray();

        $this->assertArrayHasKey('randomAdditional', $payload);
        $this->assertEquals($details['randomAdditional'], $payload['randomAdditional']);

        $this->assertArrayHasKey('random', $payload);
        $this->assertEquals($details['random'], $payload['random']);
    }

    public function testSettingTitle()
    {
        $title = bin2hex(random_bytes(2));
        $apiProblem = new ApiProblem(800, 'foo', null, $title);

        $this->assertEquals($title, $apiProblem->toArray()['title']);
    }
}
