<?php

namespace EveOnline\Regions;

use GuzzleHttp\Client;
use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use EveOnline\Logging\EveLogHandler;

class Regions {

    private $client;

    private $eveLogHandler;

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    public function regions() {
        $response = $this->client->request('GET', 'https://public-crest.eveonline.com/regions/');

        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_regions.log');
        $this->eveLogHandler->responseLog($response, $streamHandler);

        return json_decode($response->getBody()->getContents());
    }
}
