<?php

namespace EveOnline\Market\Groups;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

use EveOnline\Logging\EveLogHandler;

class MarketGroups {

    private $client;
    private $eveLogHandler;

    private $groupInformationContainer = [];
    private $createdRequests           = [];
    private $acceptedResponses         = [];
    private $rejectedResponses         = [];

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    public function fetchGroupPages() {
        $response = $this->client->request('GET', 'https://public-crest.eveonline.com/market/groups/');

        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_market_groups.log');
        $this->eveLogHandler->responseLog($response, $streamHandler);

        return json_decode($response->getBody()->getContents());
    }

    public function createRequestsForGroups(array $groups) {
        foreach($groups as $group) {
            array_push($this->createdRequests, new Request('GET', $group->types->href));
        }
    }

    public function fetchGroupsInfromation() {

        $pool = new Pool($this->client, $this->createdRequests, [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index) {
                $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_group_items_responses.log');
                $this->eveLogHandler->responseLog($response, $streamHandler);

                $responseJson                    = json_decode($response->getBody()->getContents());
                $groupPagesIterator              = new MarketGroupsPagesIterator($responseJson, $this->client, $this->eveLogHandler);
                $this->acceptedResponses[$index] = iterator_to_array($groupPagesIterator->getAllPages());
            },
            'rejected'    => function ($reason, $index)  {
                $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_group_items_response_failures.log');
                $this->eveLogHandler->responseLog($reason, $streamHandler);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    public function getAcceptedResponses() {
        return $this->acceptedResponses;
    }

    public function getGroupInformationContainer(array $acceptedResponses, array $groups) {
        if (count($acceptedResponses) > 0) {
            foreach($groups as $index => $group) {
                if (isset($acceptedResponses[$index])) {
                    $this->groupInformationContainer[$group->name] = $acceptedResponses[$index];
                }
            }

            return $this->groupInformationContainer;
        } else {
            return false;
        }
    }
}
