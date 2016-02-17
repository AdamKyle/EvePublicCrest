<?php

namespace EveOnline\Items;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;
use GuzzleHttp\Exception\ServerException;

class Details {

    private $client;

    private $eveLogHandler;

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
        $this->streamHandler = $this->eveLogHandler->setUpStreamHandler('eveonline_item_details.log');
    }

    public function details($href) {

        $response = $this->client->request('GET', $href);

        $this->eveLogHandler->responseLog($response, $this->streamHandler);

        return json_decode($response->getBody()->getContents());

    }
}
