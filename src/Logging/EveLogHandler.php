<?php

namespace EveOnline\Logging;

use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use GuzzleHttp\Psr7\Response;

/**
 * Used to create logs for the various classes.
 *
 * This log handler depends on laravel as it uses storage_path baked in.
 */
class EveLogHandler {

    /**
     * Creates an entry or a log with the response details.
     *
     * We rewind the body of the response so you can use it after its been logged.
     *
     * @param GuzzleHttp\Psr7\Response
     * @param Monolog\Handler\StreamHandler
     */
    public function responseLog(Response $response, StreamHandler $streamHandler) {
        $logInstance   = new Logger('eve_online_logger');

        $logInstance->setHandlers(array($streamHandler));
        $logInstance->addInfo('Fetched', [$response->getStatusCode(), $response->getBody()->getContents()]);
        $response->getBody()->rewind();
    }

    /**
     * Creates an entry or a log with a message
     *
     * @param mixed message
     * @param Monolog\Handler\StreamHandler
     */
    public function messageLog($message, StreamHandler $streamHandler) {
        $logInstance   = new Logger('eve_online_logger');

        $logInstance->setHandlers(array($streamHandler));
        $logInstance->addInfo('Message', [$message]);
    }

    /**
     * Required for all logs.
     *
     * Creates all logs at the storage/logs/ directory.
     *
     * @return Monolog\Handler\StreamHandler
     */
    public function setUpStreamHandler($fileName) {
        return new StreamHandler(storage_path('logs/' . $fileName), Logger::INFO);
    }
}
