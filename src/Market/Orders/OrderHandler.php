<?php

namespace EveOnline\Market\Orders;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class OrderHandler {

    private $client;

    private $requests              = [];
    private $acceptedResponsesJson = [];
    private $rejectedResponse      = [];

    public function __construct(Client $client) {
        $this->client = $client;
    }

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

    public function createRegionRequestsForPool(Array $regionHrefs) {
        $this->resetContainers();

        foreach ($regionHrefs as $regionHref) {
            array_push($this->requests, new Request('GET', $regionHref));
        }
    }

    public function processMultipleRequests(array $requests) {
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

    protected function resetContainers() {
        $this->requests              = [];
        $this->acceptedResponsesJson = [];
        $this->rejectedResponse      = [];
    }
}
