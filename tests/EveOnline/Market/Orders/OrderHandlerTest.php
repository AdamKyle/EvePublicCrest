<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

use EveOnline\Market\Orders\OrderHandler;

class OrderHandlerTest extends \PHPUnit_Framework_TestCase {

    public function fakeClient() {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['something' => 'else'])),
        ]);

        $handler = HandlerStack::create($mock);
        return new Client(['handler' => $handler]);
    }

    public function testProcessMultipleRegionsShouldNotBeEmpty() {
        $orderHandler = new OrderHandler($this->fakeClient());

        $orderHandler->createRegionRequestsForPool(['http://google.ca']);

        $pool    = $orderHandler->processMultipleRegions();
        $promise = $pool->promise();

        $promise->wait();

        $this->assertNotEmpty($orderHandler->getAcceptedResponsesJson());
    }

    public function testCreateRegionRequestsForPoolShouldNotBeEmpty() {
        $orderHandler = new OrderHandler($this->fakeClient());

        $orderHandler->createRegionRequestsForPool(['http://google.ca']);

        $this->assertNotEmpty($orderHandler->getCreatedRequests());
    }

    public function testProcessMultipleRequestsShouldNotBeEmpty() {
        $orderHandler = new OrderHandler($this->fakeClient());

        $pool    = $orderHandler->processMultipleRequests([new Request('GET', 'http://google.ca')]);
        $promise = $pool->promise();

        $promise->wait();

        $this->assertNotEmpty($orderHandler->getAcceptedResponsesJson());
    }

    public function testRejectResponseArrayShouldBeEmpty() {
        $orderHandler = new OrderHandler($this->fakeClient());

        $this->assertEmpty($orderHandler->getRejectedResponses());
    }
}
