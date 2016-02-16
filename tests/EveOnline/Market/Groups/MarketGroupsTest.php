<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Market\Groups\MarketGroups;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class GroupsTest extends \PHPUnit_Framework_TestCase {

    public function getLogMock() {
        return $this->getMockBuilder('EveOnline\Logging\EveLogHandler')
                    ->getMock();
    }

    public function testShouldGrabAllPagesOfGroups() {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['pageCount' => 2, 'types' => 'example', 'next' => ['href' => 'http://google.ca']])),
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        $logMock = $this->getLogMock();

        $logMock->method('setUpStreamHandler')
                ->with('eve_online_market_groups.log')
                ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $groups  = new MarketGroups($client, $logMock);

        $this->assertTrue(property_exists($groups->fetchGroupPages(), 'types'));
    }

    public function testShouldReturnFalseWhenWeGrabAllPagesOfGroups() {
        $mock = new MockHandler([
            new Response(302, [], json_encode(['pageCount' => 2, 'types' => 'example', 'next' => ['href' => 'http://google.ca']])),
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        $logMock = $this->getLogMock();

        $logMock->method('setUpStreamHandler')
                ->with('eve_online_market_groups.log')
                ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $groups  = new MarketGroups($client, $logMock);

        $this->assertFalse($groups->fetchGroupPages());
    }

    public function testShouldRunThePooledRequests(){
        $mock = new MockHandler([
            new Response(200, [], json_encode(['pageCount' => 1, 'types' => 'example'])),
        ]);

        $handler = HandlerStack::create($mock);

        $client  = new Client(['handler' => $handler]);
        $logMock = $this->getLogMock();

        $logMock->method('setUpStreamHandler')
                ->with('eve_online_market_groups.log')
                ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $groups = new MarketGroups($client, $logMock);

        $groups->createRequestsForGroups(json_decode(json_encode(
            [['types' => ['href' => 'http://google.ca']]]
        )));
    }

    Public function testGettingAcceptedResponsesShouldNotBeEmpty() {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['pageCount' => 1, 'types' => 'example'])),
        ]);

        $handler = HandlerStack::create($mock);

        $client  = new Client(['handler' => $handler]);
        $logMock = $this->getLogMock();

        $logMock->method('setUpStreamHandler')
                ->with('eve_online_group_items_responses.log')
                ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $groups = new MarketGroups($client, $logMock);

        $groups->createRequestsForGroups(json_decode(json_encode(
            [['types' => ['href' => 'http://google.ca']]]
        )));

        $groups->fetchGroupsInfromation();

        $this->assertNotEmpty($groups->getAcceptedResponses());
    }

    public function testReturnContainer() {
        $client  = new Client();
        $logMock = $this->getLogMock();

        $groups = new MarketGroups($client, $logMock);

        $this->assertNotEmpty($groups->getGroupInformationContainer(['something'], json_decode(json_encode([['name' => 'jesus']]))));
        $this->assertNotFalse($groups->getGroupInformationContainer(['something'], json_decode(json_encode([['name' => 'jesus']]))));
    }

    public function testReturnFalseForTheContainer() {
        $client  = new Client();
        $logMock = $this->getLogMock();

        $groups = new MarketGroups($client, $logMock);

        $this->assertFalse($groups->getGroupInformationContainer([], json_decode(json_encode([['name' => 'jesus']]))));
    }
}
