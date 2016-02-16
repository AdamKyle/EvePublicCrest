<?php

namespace EveOnline\Market\Types;

use GuzzleHttp\Client;

use EveOnline\Logging\EveLogHandler;

class Types {

    private $client;

    private $eveLogHandler;

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

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
