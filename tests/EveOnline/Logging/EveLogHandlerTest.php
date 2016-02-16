<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use EveOnline\Logging\EveLogHandler;
use GuzzleHttp\Psr7\Response;

class EveLogHandlerTest extends \PHPUnit_Framework_TestCase {

    public function testRquestLog() {
        $eveLogger = new EveLogHandler();
        $response  = new Response('200', [], json_encode(['x' => 'y']));
        $eveLogger->responseLog($response, new StreamHandler('tmp/fake.log', Logger::INFO));
    }

    public function testMessageLog() {
        $eveLogger = new EveLogHandler();
        $eveLogger->messageLog('example', new StreamHandler('tmp/fake.log', Logger::INFO));
    }
}
