<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Market\Prices\Prices;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class PricesTest extends \PHPUnit_Framework_TestCase {

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

    public function testPrices() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_market_cached_prices.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $prices   = new Prices($client, $logHandler);
        $response = $prices->prices();

        $this->assertTrue(property_exists($response, 'something'));
    }
}
