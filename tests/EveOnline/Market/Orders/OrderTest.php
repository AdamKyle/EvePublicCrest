<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Logging\EveLogHandler;
use EveOnline\Market\Orders\Order;
use EveOnline\Market\Orders\OrderHandler;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class OrderTest extends \PHPUnit_Framework_TestCase {

    public function getLogMock() {
        return $this->getMockBuilder('EveOnline\Logging\EveLogHandler')
                    ->getMock();
    }

    public function fakeClient() {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['something' => 'else'])),
        ]);

        $handler = HandlerStack::create($mock);
        return new Client(['handler' => $handler]);
    }

    public function testShouldReturnAListOfBuyOrders() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_buy_orders.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $order    = new Order($client, $logHandler);
        $response = $order->getBuyDetails('http://google.ca', json_decode(json_encode(['marketBuyOrders' => ['href' => 'http://example.com']])));

        $this->assertNotFalse($response);
    }

    public function testShouldNotReturnAListOfBuyOrders() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $order    = new Order($client, $logHandler);
        $response = $order->getBuyDetails('http://google.ca', false);

        $this->assertFalse($response);
    }

    public function testShouldReturnAListOfSellOrders() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_buy_orders.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $order    = new Order($client, $logHandler);
        $response = $order->getSellDetails('http://google.ca', json_decode(json_encode(['marketSellOrders' => ['href' => 'http://example.com']])));

        $this->assertNotFalse($response);
    }

    public function testShouldNotReturnAListOfSellOrders() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $order    = new Order($client, $logHandler);
        $response = $order->getSellDetails('http://google.ca', false);

        $this->assertFalse($response);
    }

    public function testShouldNotReturnFalseForRegionDetails() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_region_details.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $order    = new Order($client, $logHandler);
        $response = $order->getRegionDetailsJson('http://google.ca');

        $this->assertNotFalse($response);
    }

    public function testSearchAllRegions() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $order        = new Order($client, $logHandler);
        $orderHandler = new OrderHandler($client);

        $order->searchAllRegionsForOrders(['http://google.ca'], $orderHandler);

        $this->assertNotEmpty($orderHandler->getAcceptedResponsesJson());
    }

    public function testShouldCreateRequestsForMarketResponsePoolWhenBuying() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_region_details.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $order    = new Order($client, $logHandler);

        $requests = $order->createRequestsForMarketDetailsPool([
            json_decode(json_encode(['marketBuyOrders' => ['href' => 'http:://google.ca']]))
        ], 'http://google.ca', true);

        $this->assertNotEmpty($requests);
    }

    public function testShouldCreateRequestsForMarketResponsePoolWhenSelling() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_region_details.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $order    = new Order($client, $logHandler);

        $requests = $order->createRequestsForMarketDetailsPool([
            json_decode(json_encode(['marketSellOrders' => ['href' => 'http:://google.ca']]))
        ], 'http://google.ca', false);

        $this->assertNotEmpty($requests);
    }

    public function testGetTheOrdersFromRegionSearch() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $order        = new Order($client, $logHandler);
        $orderHandler = new OrderHandler($client);

        $responses = $order->getOrderResponsesFromRegionSearch($orderHandler, [new Request('GET', 'http://google.ca')]);

        $this->assertNotEmpty($responses);
    }
}
