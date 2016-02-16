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
        try {
            $response = $this->client->request('GET', $href);

            $this->eveLogHandler->responseLog($response, $this->streamHandler);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents());
            }
        } catch(ServerException $servcerException) {
            $this->eveLogHandler->messageLog('[Response Gave a 500, Tried once. Still Failed.]', $this->streamHandler);
        }

        return false;
    }
}
