<?php

namespace EveOnline\Items;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;
use GuzzleHttp\Exception\ServerException;

/**
 * Grabs the items details based off the item url.
 */
class Details {

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
        $this->streamHandler = $this->eveLogHandler->setUpStreamHandler('eveonline_item_details.log');
    }

    /**
     * When passed a url you will get back the items details.
     *
     * Example url: https://public-crest.eveonline.com/types/32772/
     *
     * @param string url - example: https://public-crest.eveonline.com/types/32772/
     * @return decoded json
     */
    public function details($href) {

        $response = $this->client->request('GET', $href);

        $this->eveLogHandler->responseLog($response, $this->streamHandler);

        return json_decode($response->getBody()->getContents());

    }
}
