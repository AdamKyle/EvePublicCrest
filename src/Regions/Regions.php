<?php

namespace EveOnline\Regions;

use GuzzleHttp\Client;

/**
 * Fetches the Eve Online Regions
 */
class Regions {

    /**
     * Guzzle Client.
     */
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * Returns the response pertaining to the regions.
     *
     * @param function callback - Response call back that takes an argument of GuzzleHttp\Psr7\Response $response
     * @return function callback
     */
    public function regions($callbackFunction) {
        $response = $this->client->request('GET', 'https://crest-tq.eveonline.com/regions/');

        return call_user_func_array($callbackFunction, array($response));
    }
}
