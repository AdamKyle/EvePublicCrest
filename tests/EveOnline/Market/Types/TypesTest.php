<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Market\Types\Types;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TypesTest extends \PHPUnit_Framework_TestCase {

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

    public function invokeMethod(&$object, $methodName, array $parameters = array()){
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);

        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testGetTypes() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_market_types.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $types    = new Types($client, $logHandler);
        $response = $types->fetchTypes();

        $this->assertNotEmpty($response);
    }

    public function testThatMorePagesAreReturned() {
        $client     = $this->fakeClient();
        $logHandler = $this->getLogMock();

        $logHandler->method('setUpStreamHandler')
                   ->with('eve_online_market_types.log')
                   ->willReturn(new StreamHandler('tmp/something.log', Logger::INFO));

        $types     = new Types($client, $logHandler);
        $morePages = $this->invokeMethod($types, 'getOtherPages', array(json_decode(json_encode(
            ['next' => ['href' => 'http://google.ca']]
        ))));

        $this->assertNotEmpty(iterator_to_array($morePages));
    }
}
