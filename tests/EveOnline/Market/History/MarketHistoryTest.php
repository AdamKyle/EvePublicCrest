<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Logging\EveLogHandler;
use EveOnline\Market\History\MarketHistory;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MarketHistoryTest extends \PHPUnit_Framework_TestCase {

    public function getLogMock() {
        return $this->getMockBuilder('EveOnline\Logging\EveLogHandler')
                    ->getMock();
    }

    public function getPartialHistoryClassMock(Client $client, EveLogHandler $eveLogHandlerMock) {
        return $this->getMock(
            'EveOnline\Market\History\MarketHistory',
            ['getOptions'],
            [$client, $eveLogHandlerMock]
        );
    }

    public function testShouldRunTheRequestsViaThePool() {
        $headers = [];

        $handler = new MockHandler([
            function (Request $request) use (&$headers) {
                $headers[] = $request;
                return new Response(200, [], json_encode(['pageCount' => 1, 'types' => 'example', 'items' => [1, 2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0]]));
            }
        ]);

        $client  = new Client(['handler' => $handler]);
        $logMock = $this->getLogMock();

        $historyMock = $this->getPartialHistoryClassMock($client, $logMock);

        $historyMock->expects($this->once())
                    ->method('getOptions')
                    ->with(-20, function($regionAndItemPairs, $responseJson){})
                    ->willReturn(['options' => ['headers' => ['x-foo' => 'bar']]]);

        $historyMock->createRequests([1, 2, 3, 4, 5], [1, 2, 3, 4, 5]);
        $historyMock->getItemHistoryForRegion(-20, function($regionAndItemPairs, $responseJson) {
            $this->assertNotEmpty($responseJson->items);
            $this->assertNotEmpty($regionAndItemPairs);
        });
    }

    public function testShouldTestTheWholeProcess() {

        $mock = new MockHandler([
            new Response(200, [], json_encode(['pageCount' => 1, 'types' => 'example', 'items' => [1, 2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0]])),
        ]);

        $handler = HandlerStack::create($mock);

        $client  = new Client(['handler' => $handler]);
        $logMock = $this->getLogMock();

        $marketHistory = new MarketHistory($client, $logMock);
        $marketHistory->createRequests([1, 2, 3, 4, 5], [1, 2, 3, 4, 5]);

        $logMock->method('setUpStreamHandler')
                ->with('eve_online_region_item_history_responses.log')
                ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $marketHistory->getItemHistoryForRegion(-20, function($regionAndItemPairs, $responseJson) {
            $this->assertNotEmpty($responseJson->items);
            $this->assertNotEmpty($regionAndItemPairs);
        });
    }
}
