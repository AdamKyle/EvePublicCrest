<?php

namespace EveOnline\Regions;

use GuzzleHttp\Client;
use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use EveOnline\Logging\EveLogHandler;

/**
 * Fetches the Eve Online Regions
 */
class Regions {

    /**
     * Guzzle Client.
     */
    private $client;

    /**
     * Custom Eve Log Handler.
     *
     * @see EveOnline\Logging\EveLogHandler
     */
    private $eveLogHandler;

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    /**
     * Returns the JSOn pertaining to the regions.
     *
     * @return JSON - json is decoded.
     */
    public function regions() {
        $response = $this->client->request('GET', 'https://public-crest.eveonline.com/regions/');

        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_regions.log');
        $this->eveLogHandler->responseLog($response, $streamHandler);

        return json_decode($response->getBody()->getContents());
    }
}
