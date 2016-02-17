<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class DetailsTest extends \PHPUnit_Framework_TestCase {

    public function getLogMock() {
        return $this->getMockBuilder('EveOnline\Logging\EveLogHandler')
                    ->getMock();
    }

    public function testDetailsDoesNotReturnFalse() {

        $logMock = $this->getLogMock();

        $logMock->method('setUpStreamHandler')
                ->with('eveonline_item_details.log')
                ->willReturn(new StreamHandler('tmp/logName.com', Logger::INFO));

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
        ]);

        $handler  = HandlerStack::create($mock);
        $client   = new Client(['handler' => $handler]);
        $details  = new EveOnline\Items\Details($client, $logMock);
        $response = $details->details('example');

        $this->assertNotFalse($response);
    }
}
