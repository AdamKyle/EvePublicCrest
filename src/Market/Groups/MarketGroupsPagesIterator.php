<?php

namespace EveOnline\Market\Groups;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;

/**
 * Used to fetch the individual pages of a group.
 *
 * Each group can contain a set of pages describing what items exist with in
 * that group. These are fetched and returned as an Itterator.
 */
class MarketGroupsPagesIterator {

    /**
     * The response JSON for a single group
     */
    private $responseJson;

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

    public function __construct($responseJson, Client $client, EveLoghandler $eveLogHandler) {

        $this->responseJson  = $responseJson;
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    /**
     * Responsible for returning all pages.
     *
     * Yields out the JSON for each page assuming that the specific
     * group contains the property: next.
     */
    public function getAllPages() {

        $responseJson = $this->responseJson;

        yield $responseJson;

        while (property_exists($responseJson, 'next')) {
            $response = $this->client->request('GET', $responseJson->next->href);

            $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_item_response_addition_pages.log');
            $this->eveLogHandler->responseLog($response, $streamHandler);

            if ($response->getStatusCode() === 200) {
                $responseJson = json_decode($response->getBody()->getContents());

                $response->getBody()->rewind();

                yield json_decode($response->getBody()->getContents());
            }
        }
    }
}
