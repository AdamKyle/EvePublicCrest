<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class DetailsTest extends \PHPUnit_Framework_TestCase {

    public function testDetailsDoesNotReturnFalse() {

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
        ]);

        $handler  = HandlerStack::create($mock);
        $client   = new Client(['handler' => $handler]);
        $details  = new EveOnline\Items\Details($client);
        $response = $details->details('example', function($response){ });

        $this->assertNotFalse($response);
    }
}
