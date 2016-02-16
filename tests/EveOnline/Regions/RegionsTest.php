<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Regions\Regions;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class RegionsTest extends \PHPUnit_Framework_TestCase {
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

    public function testGetRegions() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_regions.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $regions  = new Regions($client, $logHandler);
        $response = $regions->regions();

        $this->assertTrue(property_exists($response, 'something'));
    }
}
