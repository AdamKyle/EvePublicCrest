<?php

namespace EveOnline\Market\Orders;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

/**
 * Used with the Order Class to handle requests.
 */
class OrderHandler {


    /**
     * Guzzle Client.
     */
    private $client;

    /**
     * Array of GuzzleHttp\Psr7\Request
     */
    private $requests              = [];

    /**
     * Array of use GuzzleHttp\Psr7\Response;
     */
    private $acceptedResponsesJson = [];

    /**
     * Array of reasons why the request failed.
     */
    private $rejectedResponse      = [];

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * Processes multiple regions pushing accpted requests to an array.
     *
     * Uses 18 concurrent connections.
     */
    public function processMultipleRegions() {
        return new Pool($this->client, $this->requests, [
            'concurrency' => 18,
            'fulfilled'   => function ($response, $index) {
                array_push($this->acceptedResponsesJson, json_decode($response->getBody()->getContents()));
            },
            'rejected'    => function ($reason, $index)  {
                array_push($this->rejectedResponse, $reason);
            },
        ]);
    }

    /**
     * Create a set of region requests for a pool.
     *
     * @param array of region hrefs, example: https://crest-tq.eveonline.com/regions/11000001/
     */
    public function createRegionRequestsForPool(Array $regionHrefs) {
        $this->resetContainers();

        foreach ($regionHrefs as $regionHref) {
            array_push($this->requests, new Request('GET', $regionHref));
        }
    }

    /**
     * Processes multiple requests at a time.
     *
     * Uses 18 concurrent connections.
     *
     * We also reset the responses container.
     *
     * @param array of GuzzleHttp\Psr7\Request
     */
    public function processMultipleRequests(array $requests) {
        $this->resetResponsesContainer();

        return new Pool($this->client, $requests, [
            'concurrency' => 18,
            'fulfilled'   => function ($response, $index) {
                array_push($this->acceptedResponsesJson, json_decode($response->getBody()->getContents()));
            },
            'rejected'    => function ($reason, $index) {
                array_push($this->rejectedResponse, $reason);
            },
        ]);
    }

    public function getAcceptedResponsesJson() {
        return $this->acceptedResponsesJson;
    }

    public function getRejectedResponses() {
        return $this->rejectedResponse;
    }

    public function getCreatedRequests() {
        return $this->requests;
    }

    public function resetResponsesContainer() {
        $this->acceptedResponsesJson = [];
        $this->rejectedResponse      = [];
    }

    protected function resetContainers() {
        $this->requests              = [];
        $this->acceptedResponsesJson = [];
        $this->rejectedResponse      = [];
    }
}
