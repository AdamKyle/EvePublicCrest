<?php

namespace EveOnline\Market\Prices;

use GuzzleHttp\Client;
use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use EveOnline\Logging\EveLogHandler;

class Prices {

    private $client;

    private $eveLogHandler;

    public function __construct(Client $client, EveLoghandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    public function prices() {

        $response = $this->client->request('GET', 'https://public-crest.eveonline.com/market/prices/');

        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_market_cached_prices.log');
        $this->eveLogHandler->responseLog($response, $streamHandler);

        return json_decode($response->getBody()->getContents());
    }

    public function ItemType($url) {

        $response = $this->client->request('GET', $url);

        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_item_type.log');
        $this->eveLogHandler->responseLog($response, $streamHandler);

        return json_decode($response->getBody()->getContents());
    }
}
