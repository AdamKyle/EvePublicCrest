<?php

namespace EveOnline\Market\Prices;

use GuzzleHttp\Client;
use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use EveOnline\Logging\EveLogHandler;

class Prices {

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

    public function __construct(Client $client, EveLoghandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    /**
     * Responsible for returning a set of prices.
     *
     * Grabs all of the market prices: https://public-crest.eveonline.com/market/prices/
     *
     * @return decoded json of https://public-crest.eveonline.com/market/prices/
     */
    public function prices() {

        $response = $this->client->request('GET', 'https://public-crest.eveonline.com/market/prices/');

        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_market_cached_prices.log');
        $this->eveLogHandler->responseLog($response, $streamHandler);

        return json_decode($response->getBody()->getContents());
    }
}
