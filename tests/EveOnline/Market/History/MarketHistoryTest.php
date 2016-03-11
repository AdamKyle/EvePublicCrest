<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

use EveOnline\Market\History\MarketHistory;

class MarketHistoryTest extends \PHPUnit_Framework_TestCase {

    public function getPartialHistoryClassMock(Client $client) {
        return $this->getMock(
            'EveOnline\Market\History\MarketHistory',
            ['getOptions'],
            [$client]
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

        $client      = new Client(['handler' => $handler]);
        $historyMock = $this->getPartialHistoryClassMock($client);

        $historyMock->expects($this->once())
                    ->method('getOptions')
                    ->with(-20, function($regionAndItemPairs, $responseJson){})
                    ->willReturn(['options' => ['headers' => ['x-foo' => 'bar']]]);

        $historyMock->createRequests([1, 2, 3, 4, 5], [1, 2, 3, 4, 5]);
        $historyMock->getItemHistoryForRegion(-20, function($regionAndItemPairs, $responseJson) {
            $this->assertNotEmpty($responseJson->items);
            $this->assertNotEmpty($regionAndItemPairs);
        }, function($reason, $index) { /* Do something with the reason it failed here ... */ });
    }

    public function testShouldTestTheWholeProcess() {

        $mock = new MockHandler([
            new Response(200, [], json_encode(['pageCount' => 1, 'types' => 'example', 'items' => [1, 2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0]])),
        ]);

        $handler       = HandlerStack::create($mock);
        $client        = new Client(['handler' => $handler]);
        $marketHistory = new MarketHistory($client);

        $marketHistory->createRequests([1, 2, 3, 4, 5], [1, 2, 3, 4, 5]);

        $marketHistory->getItemHistoryForRegion(-20, function($regionAndItemPairs, $responseJson) {
            $this->assertNotEmpty($responseJson->items);
            $this->assertNotEmpty($regionAndItemPairs);
        }, function($reason, $index) { /* Do something with the reason it failed here ... */ });
    }
}
