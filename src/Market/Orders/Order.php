<?php

namespace EveOnline\Market\Orders;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Fetches all Buy/Sell orders or a region or all regions.
 */
class Order {

    /**
     * Guzzle Client.
     */
    private $client;

    public function __construct(Client $client) {
        $this->client        = $client;
    }

    /**
     * Gets the buying details for a spefic item from a specific region.
     *
     * This function can return false meaning there are no buying orders for the
     * that region.
     *
     * @param string $itemTypeHref, example: https://public-crest.eveonline.com/types/32772/
     * @param json the region details json, example: https://public-crest.eveonline.com/regions/11000001/
     * @return decoded json or false
     */
    public function getBuyDetails($itemTypeHref, $regionDetailsJson) {
        if (!$regionDetailsJson) {
            return false;
        }

        return $this->getBuyOrders($regionDetailsJson->marketBuyOrders->href, $itemTypeHref);
    }

    /**
     * Gets the selling details for a spefic item from a specific region.
     *
     * This function can return false meaning there are no selling orders for the
     * that region.
     *
     * @param string $itemTypeHref, example: https://public-crest.eveonline.com/types/32772/
     * @param json the region details json, example: https://public-crest.eveonline.com/regions/11000001/
     * @return decoded json or false
     */
    public function getSellDetails($itemTypeHref, $regionDetailsJson) {
        if (!$regionDetailsJson) {
            return false;
        }

        return $this->getBuyOrders($regionDetailsJson->marketSellOrders->href, $itemTypeHref);
    }

    /**
     * Gets the region json for a specific region href.
     *
     * For example: https://public-crest.eveonline.com/regions/11000001/
     *
     * @param string regionHref, example: https://public-crest.eveonline.com/regions/11000001/
     * @return json
     */
    public function getRegionDetailsJson($regionHref) {
        return $this->getRegionDetails($regionHref);
    }

    /**
     * Searches All Regions for orders.
     *
     * Searches all regions for any orders.
     *
     * @param array of region urls to search through.
     * @param OrderHandler $orderHandler
     */
    public function searchAllRegionsForOrders(Array $regionHrefs, OrderHandler $orderHandler) {
        $orderHandler->createRegionRequestsForPool($regionHrefs);

        $pool         = $orderHandler->processMultipleRegions();
        $promise      = $pool->promise();

        $promise->wait();
    }

    /**
     * Creates a set of requests for the market details.
     *
     * Uses an array of responses and the item type we are searching for to see if there
     * there are orders. The final argument determines if we want buying (true) or selling (false)
     * orders for all regions listed.
     *
     * @param array decoded JSON responses
     * @param string item type href, example: https://public-crest.eveonline.com/types/32772/
     * @param boolean isBuying, true - yes, false - no.
     * @return array of GuzzleHttp\Psr7\Request
     */
    public function createRequestsForMarketDetailsPool(array $responses, $itemTypeHref, $isBuying) {
        return $this->getRegionOrderRequestsForPool($responses, $isBuying, $itemTypeHref);
    }

    /**
     * Gets all the details about the market for a specific item
     *
     * Based on `searchAllRegionsForOrders`, we then use the `createRequestsForMarketDetailsPool` to createdRequests
     * responses that then get used with this function to give you all the regions selling that item and there associated
     * details.
     *
     * @param OrderHandler $orderHandler
     * @param array of GuzzleHttp\Psr7\Request
     * @return array of decoded json
     */
    public function getOrderResponsesFromRegionSearch(OrderHandler $orderHandler, array $createdRequests) {
        $pool            = $orderHandler->processMultipleRequests($createdRequests);
        $promise         = $pool->promise();

        $promise->wait();

        return $orderHandler->getAcceptedResponsesJson();
    }

    protected function getRegionDetails($regionHref) {
        $response = $this->client->request('GET', $regionHref);

        return json_decode($response->getBody()->getContents());
    }

    protected function getBuyOrders($regionBuyLink, $itemTypeHref) {
        $response = $this->client->request('GET', $regionBuyLink . '?type=' . $itemTypeHref);

        return json_decode($response->getBody()->getContents());
    }

    private function getRegionOrderRequestsForPool(array $responses, $isBuying, $itemTypeHref) {
        $requestsForPool = [];

        foreach ($responses as $response) {
            if ($isBuying) {
                array_push($requestsForPool, new Request('GET', $response->marketBuyOrders->href . '?type=' . $itemTypeHref));
            } else {
                array_push($requestsForPool, new Request('GET', $response->marketSellOrders->href . '?type=' . $itemTypeHref));
            }
        }

        return $requestsForPool;
    }
}
