<?php

namespace EveOnline\Market\History;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use EveOnline\Logging\EveLogHandler;
use \Carbon\Carbon;

class MarketHistory {

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

    /**
     * A set of Guzzle Requests.
     */
    private $createdRequests    = [];

    /**
     * A set of accepted responses from the pool.
     */
    private $acceptedResponses  = [];

    /**
     * The actual historical data for the item and region.
     */
    private $historicalData     = [];

    /**
     * The region and item id pairs.
     */
    private $regionAndItemPairs = [];

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    /**
     * Create a set of requests.
     *
     * Creates the requests based off the item id's and the region id's which are paired
     * together for later use in compiling the historical data.
     *
     * @param array of item id's
     * @param array of region id's
     */
    public function createRequests(array $items, array $regions) {
        foreach($items as $item) {
            foreach ($regions as $region) {
                array_push($this->regionAndItemPairs, [$item, $region]);
                array_push($this->createdRequests, new Request('GET', 'https://public-crest.eveonline.com/market/'.$region.'/types/'.$item.'/history/'));
            }
        }
    }

    /**
     * Get the item history for the a region.
     */
    public function getItemHistoryForRegion() {

        $pool = new Pool($this->client, $this->createdRequests, $this->getOptions());

        $promise = $pool->promise();
        $promise->wait();
    }

    /**
     * Gets the historical data.
     *
     * Each array contains a regionId key, ItemId key and the responseJSON key.
     *
     * @return array of arrays.
     */
    public function getHistoricalData() {
        return end($this->historicalData);
    }

    protected function getOptions() {
        return [
            'concurrency' => 18,
            'fulfilled'   => function ($response, $index) {

                $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_region_item_history_responses.log');
                $this->eveLogHandler->responseLog($response, $streamHandler);

                $responseJson                    = json_decode($response->getBody()->getContents());
                $this->acceptedResponses[$index] = $responseJson;

                $this->populateHistoricalDataContainer($responseJson, $index);
            },
            'rejected'    => function ($reason, $index)  {
                $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_region_item_history_responses.log');
                $this->eveLogHandler->messageLog($reason, $streamHandler);
            },
        ];
    }

    protected function populateHistoricalDataContainer($responseJson, $index) {
        if ($responseJson->pageCount !== 0) {
            $this->acceptedResponses[$index] = $responseJson;

            $historyDetails = new MarketHistoryDetails($this->acceptedResponses, $this->regionAndItemPairs);
            $historyDetails->createHistoryDetails();

            array_push($this->historicalData, $historyDetails->getHistoryDetails());
        }
    }
}
