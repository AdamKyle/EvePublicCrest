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
                array_push($this->regionAndItemPairs, [$region, $item]);
                array_push($this->createdRequests, new Request('GET', 'https://public-crest.eveonline.com/market/'.$region.'/types/'.$item.'/history/'));
            }
        }
    }

    /**
     * Get the item history for the a region.
     *
     * You can specificy the response json items to come back. This means
     * that you can say something like -20, this will give you the last 20
     * items of the array of response->items.
     *
     * The second argument is a callback that takes the following paramters:
     *
     * - array regionItemPairs, example: [0, 1] where 0 is the region id and 1 is the item id.
     * - json object responseJson which is the response json from the promise.
     *
     * This call back function can then be used to do what ever you wish with the data backing back.
     * the region and item pairs map to the response json in question.
     *
     * @param $howManyItemsBack How many items should we get back?
     * @param $callBackFunction the function which takes an array of region id and item id and a responseJson object.
     */
    public function getItemHistoryForRegion($howManyItemsBack, $callBackFunction) {

        $pool = new Pool($this->client, $this->createdRequests, $this->getOptions($howManyItemsBack, $callBackFunction));

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
        return $this->historicalData;
    }

    protected function getOptions($howManyItemsBack, $callBackFunction) {
        return [
            'concurrency' => 18,
            'fulfilled'   => function ($response, $index) use (&$howManyItemsBack, &$callBackFunction) {

                $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_region_item_history_responses.log');
                $this->eveLogHandler->responseLog($response, $streamHandler);

                $responseJson         = json_decode($response->getBody()->getContents());
                $responseJson->items  = array_slice($responseJson->items, $howManyItemsBack);

                call_user_func_array($callBackFunction, array($this->regionAndItemPairs[$index], $responseJson));
            },
            'rejected'    => function ($reason, $index)  {
                $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_region_item_history_responses.log');
                $this->eveLogHandler->messageLog($reason, $streamHandler);
            },
        ];
    }
}
