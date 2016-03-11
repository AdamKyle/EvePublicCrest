<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Market\Groups\MarketGroups;

class GroupsTest extends \PHPUnit_Framework_TestCase {

    public function testShouldGrabAllPagesOfGroups() {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['pageCount' => 2, 'types' => 'example', 'next' => ['href' => 'http://google.ca']])),
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        $groups  = new MarketGroups($client);

        $groups->fetchGroupPages(function($response){
            $this->assertInstanceOf(Response::class, $response);

            $json = json_decode($response->getBody()->getContents());
            $this->assertTrue(property_exists($json, 'pageCount'));
        });
    }

    public function testShouldRunThePooledRequests(){
        $mock = new MockHandler([
            new Response(200, [], json_encode(['pageCount' => 1, 'types' => 'example'])),
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);
        $groups  = new MarketGroups($client);

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
        $groups  = new MarketGroups($client);

        $groups->createRequestsForGroups(json_decode(json_encode(
            [['types' => ['href' => 'http://google.ca']]]
        )));

        $groups->fetchGroupsInfromation(function($reason, $index){ /* Do something with the reason why it failed. */ });

        $this->assertNotEmpty($groups->getAcceptedResponses());
    }

    public function testReturnContainer() {
        $client  = new Client();
        $groups  = new MarketGroups($client);

        $this->assertNotEmpty($groups->getGroupInformationContainer(['something'], json_decode(json_encode([['name' => 'jesus']]))));
        $this->assertNotFalse($groups->getGroupInformationContainer(['something'], json_decode(json_encode([['name' => 'jesus']]))));
    }

    public function testReturnFalseForTheContainer() {
        $client  = new Client();
        $groups  = new MarketGroups($client);

        $this->assertFalse($groups->getGroupInformationContainer([], json_decode(json_encode([['name' => 'jesus']]))));
    }
}
