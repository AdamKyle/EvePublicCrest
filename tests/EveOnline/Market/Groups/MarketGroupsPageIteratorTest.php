<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Market\Groups\MarketGroupsPagesIterator;

class MarketGroupPagesIteratorTest extends \PHPUnit_Framework_TestCase {

    public function testShouldReturnAnArrayWhenThereAreMorePages() {

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['something' => 'else'])),
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        $groupsPageIterator = new MarketGroupsPagesIterator(json_decode(json_encode(['next' => ['href' => 'http://google.ca']])), $client);

        $iterator = iterator_to_array($groupsPageIterator->getAllPages());

        $this->assertInstanceOf(\Generator::class, $groupsPageIterator->getAllPages());
        $this->assertNotEmpty($groupsPageIterator);
    }

    public function testShouldReturnAnArrayWhenThereAreNoMorePages() {

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['something' => 'else'])),
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        $groupsPageIterator = new MarketGroupsPagesIterator(json_decode(json_encode(['foo' => 'bar'])), $client);

        $iterator = iterator_to_array($groupsPageIterator->getAllPages());

        $this->assertInstanceOf(\Generator::class, $groupsPageIterator->getAllPages());
        $this->assertNotEmpty($groupsPageIterator);
    }
}
