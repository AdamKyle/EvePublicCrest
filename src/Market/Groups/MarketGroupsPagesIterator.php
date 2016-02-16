<?php

namespace EveOnline\Market\Groups;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;

class MarketGroupsPagesIterator {

    private $responseJson;

    private $client;

    private $eveLogHandler;

    public function __construct($responseJson, Client $client, EveLoghandler $eveLogHandler) {

        $this->responseJson  = $responseJson;
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

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
