<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Market\Groups\MarketGroupsPagesIterator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MarketGroupPagesIteratorTest extends \PHPUnit_Framework_TestCase {

    public function getLogMock() {
        return $this->getMockBuilder('EveOnline\Logging\EveLogHandler')
                    ->getMock();
    }

    public function testShouldReturnAnArrayWhenThereAreMorePages() {
        $logMock = $this->getLogMock();

        $logMock->method('setUpStreamHandler')
                ->with('eve_online_item_response_addition_pages.log')
                ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['something' => 'else'])),
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        $groupsPageIterator = new MarketGroupsPagesIterator(json_decode(json_encode(['next' => ['href' => 'http://google.ca']])), $client, $logMock);

        $iterator = iterator_to_array($groupsPageIterator->getAllPages());

        $this->assertInstanceOf(\Generator::class, $groupsPageIterator->getAllPages());
        $this->assertNotEmpty($groupsPageIterator);
    }

    public function testShouldReturnAnArrayWhenThereAreNoMorePages() {
        $logMock = $this->getLogMock();

        $logMock->method('setUpStreamHandler')
                ->with('eve_online_item_response_addition_pages.log')
                ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['something' => 'else'])),
        ]);

        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        $groupsPageIterator = new MarketGroupsPagesIterator(json_decode(json_encode(['foo' => 'bar'])), $client, $logMock);

        $iterator = iterator_to_array($groupsPageIterator->getAllPages());

        $this->assertInstanceOf(\Generator::class, $groupsPageIterator->getAllPages());
        $this->assertNotEmpty($groupsPageIterator);
    }
}
