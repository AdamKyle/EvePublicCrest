<?php

namespace EveOnline\Items;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;

/**
 * Grabs the items details based off the item url.
 */
class Details {

    /**
     * Guzzle Client.
     */
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * When passed a url you will get back the items details.
     *
     * Example url: https://crest-tq.eveonline.com/types/32772/
     *
     * @param string url - example: https://crest-tq.eveonline.com/types/32772/
     * @param function callback - Response call back that takes an argument of GuzzleHttp\Psr7\Response $response
     * @return callback function with the response injected.
     */
    public function details($href, $responseCallBack) {

        $response = $this->client->request('GET', $href);
        return call_user_func_array($responseCallBack, array($response));
    }
}
