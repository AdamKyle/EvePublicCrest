<?php

namespace EveOnline\Market\Types;

use GuzzleHttp\Client;

use EveOnline\Logging\EveLogHandler;

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
     * Fetches the market types.
     *
     * Will return all 13 pages of market types as json.
     *
     * @return Array of json decoded responses
     */
    public function fetchTypes() {
        $response = $this->client->request('GET', 'https://public-crest.eveonline.com/market/types/');

        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_market_types.log');
        $this->eveLogHandler->responseLog($response, $streamHandler);

        return iterator_to_array($this->getOtherPages(json_decode($response->getBody()->getContents())));
    }

    protected function getOtherPages($responseJson) {

        yield $responseJson;

        while(property_exists($responseJson, 'next')) {
            $response = $this->client->request('GET', $responseJson->next->href);

            $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_market_types.log');
            $this->eveLogHandler->responseLog($response, $streamHandler);

            $responseJson = json_decode($response->getBody()->getContents());
            $response->getBody()->rewind();

            yield json_decode($response->getBody()->getContents());
        }
    }
}
