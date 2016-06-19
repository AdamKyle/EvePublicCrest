<?php

namespace EveOnline\Market\Prices;

use GuzzleHttp\Client;

class Prices {

    /**
     * Guzzle Client.
     */
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * Responsible for returning a set of prices.
     *
     * Grabs all of the market prices: https://crest-tq.eveonline.com/market/prices/
     *
     * @param function callback - Response call back that takes an argument of GuzzleHttp\Psr7\Response $response
     * @return function callback
     */
    public function prices($callbackFunction) {

        $response = $this->client->request('GET', 'https://crest-tq.eveonline.com/market/prices/');

        return call_user_func_array($callbackFunction, array($response));
    }
}
