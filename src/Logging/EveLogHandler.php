<?php

namespace EveOnline\Logging;

use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use GuzzleHttp\Psr7\Response;

class EveLogHandler {

    public function responseLog(Response $response, StreamHandler $streamHandler) {
        $logInstance   = new Logger('eve_online_logger');

        $logInstance->setHandlers(array($streamHandler));
        $logInstance->addInfo('Fetched', [$response->getStatusCode(), $response->getBody()->getContents()]);
        $response->getBody()->rewind();
    }

    public function messageLog($message, StreamHandler $streamHandler) {
        $logInstance   = new Logger('eve_online_logger');

        $logInstance->setHandlers(array($streamHandler));
        $logInstance->addInfo('Message', [$message]);
    }

    public function setUpStreamHandler($fileName) {
        return new StreamHandler(storage_path('logs/' . $fileName), Logger::INFO);
    }
}
