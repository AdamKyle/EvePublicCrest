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

    private $client;
    private $eveLogHandler;
    private $pool;

    private $createdRequests    = [];
    private $acceptedResponses  = [];
    private $rejectedResponses  = [];
    private $historicalData     = [];
    private $regionAndItemPairs = [];

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    public function createRequests(array $items, array $regions) {
        foreach($items as $item) {
            foreach ($regions as $region) {
                array_push($this->regionAndItemPairs, [$item, $region]);
                array_push($this->createdRequests, new Request('GET', 'https://public-crest.eveonline.com/market/'.$region.'/types/'.$item.'/history/'));
            }
        }
    }

    public function getItemHistoryForRegion() {

        $pool = new Pool($this->client, $this->createdRequests, $this->getOptions());

        $promise = $pool->promise();
        $promise->wait();
    }

    public function getHistoricalData() {
        return $this->historicalData;
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
