<?php

namespace EveOnline\Market\Types;

use GuzzleHttp\Client;

/**
 * Fetches the Eve Online market types
 *
 * Because there is (at the time of this documentation) 13 pages of types
 * we also have an itterator that goes through each page fetching the infaormation.
 *
 * The result is an array of responses that you can then use.
 */
class Types {

    /**
     * Guzzle Client.
     */
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * Fetches the market types.
     *
     * Will return all 13 pages of market types as json.
     *
     * @return Array of json decoded responses
     */
    public function fetchTypes() {
        $response = $this->client->request('GET', 'https://crest-tq.eveonline.com/market/types/');

        return iterator_to_array($this->getOtherPages(json_decode($response->getBody()->getContents())));
    }

    protected function getOtherPages($responseJson) {

        yield $responseJson;

        while(property_exists($responseJson, 'next')) {
            $response     = $this->client->request('GET', $responseJson->next->href);
            $responseJson = json_decode($response->getBody()->getContents());
            
            $response->getBody()->rewind();

            yield json_decode($response->getBody()->getContents());
        }
    }
}
