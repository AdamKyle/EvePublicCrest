<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use EveOnline\Market\Prices\Prices;

class PricesTest extends \PHPUnit_Framework_TestCase {

    public function fakeClient() {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['something' => 'else'])),
        ]);

        $handler = HandlerStack::create($mock);
        return new Client(['handler' => $handler]);
    }

    public function testPrices() {
        $client   = $this->fakeClient();
        $prices   = new Prices($client);

        $prices->prices(function($response) {
            $this->assertInstanceOf(Response::class, $response);

            $json = json_decode($response->getBody()->getContents());
            $this->assertTrue(property_exists($json, 'something'));
        });
    }
}
