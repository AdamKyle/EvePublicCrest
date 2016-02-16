<?php

namespace EveOnline\Market\Orders;

use Illuminate\Database\Eloquent\Collection;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use EveOnline\Logging\EveLogHandler;

class Order {

    private $client;

    private $eveLogHandler;

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    public function getBuyDetails($itemTypeHref, $regionDetailsJson) {
        if (!$regionDetailsJson) {
            return false;
        }

        return $this->getBuyOrders($regionDetailsJson->marketBuyOrders->href, $itemTypeHref);
    }

    public function getSellDetails($itemTypeHref, $regionDetailsJson) {
        if (!$regionDetailsJson) {
            return false;
        }

        return $this->getBuyOrders($regionDetailsJson->marketSellOrders->href, $itemTypeHref);
    }

    public function getRegionDetailsJson($regionHref) {
        return $this->getRegionDetails($regionHref);
    }

    public function searchAllRegionsForOrders(Array $regionHrefs, OrderHandler $orderHandler) {
        $orderHandler->createRegionRequestsForPool($regionHrefs);

        $pool         = $orderHandler->processMultipleRegions();
        $promise      = $pool->promise();

        $promise->wait();
    }

    public function createRequestsForMarketDetailsPool(array $responses, $itemTypeHref, $isBuying) {
        return $this->getRegionOrderRequestsForPool($responses, $isBuying, $itemTypeHref);
    }

    public function getOrderResponsesFromRegionSearch(OrderHandler $orderHandler, array $createdRequests) {
        $pool            = $orderHandler->processMultipleRequests($createdRequests);
        $promise         = $pool->promise();

        $promise->wait();

        return $orderHandler->getAcceptedResponsesJson();
    }

    protected function getRegionDetails($regionHref) {

        $response      = $this->client->request('GET', $regionHref);
        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_region_details.log');

        $this->eveLogHandler->responseLog($response, $streamHandler);

        return json_decode($response->getBody()->getContents());
    }

    protected function getBuyOrders($regionBuyLink, $itemTypeHref) {

        $response      = $this->client->request('GET', $regionBuyLink . '?type=' . $itemTypeHref);
        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_buy_orders.log');

        $this->eveLogHandler->responseLog($response, $streamHandler);

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

            $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_region_details.log');
            $this->eveLogHandler->messageLog($response, $streamHandler);
        }

        return $requestsForPool;
    }
}
