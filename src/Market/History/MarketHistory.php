<?php

namespace EveOnline\Market\History;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use \Carbon\Carbon;

class MarketHistory {

    /**
     * Guzzle Client.
     */
    private $client;

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

    public function __construct(Client $client) {
        $this->client = $client;
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
     * @param $successCallbackFunction the function which takes an array of region id and item id and a responseJson object.
     * @param $rejectedCallbackFunction the function to handle rejected response. $reason and $index are injected into the function.
     */
    public function getItemHistoryForRegion($howManyItemsBack, $successCallbackFunction, $rejectedCallbackFunction) {

        $pool = new Pool($this->client, $this->createdRequests, $this->getOptions($howManyItemsBack, $successCallbackFunction, $rejectedCallbackFunction));

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

    protected function getOptions($howManyItemsBack, $successCallbackFunction, $rejectedCallbackFunction) {
        return [
            'concurrency' => 18,
            'fulfilled'   => function ($response, $index) use (&$howManyItemsBack, &$successCallbackFunction) {

                $responseJson         = json_decode($response->getBody()->getContents());
                $responseJson->items  = array_slice($responseJson->items, $howManyItemsBack);

                call_user_func_array($successCallbackFunction, array($this->regionAndItemPairs[$index], $responseJson));
            },
            'rejected'    => function ($reason, $index) use (&$rejectedCallbackFunction)  {
                call_user_func_array($rejectedCallbackFunction, array($reason, $index));
            },
        ];
    }
}
