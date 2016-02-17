<?php

namespace EveOnline\Market\Groups;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

use EveOnline\Logging\EveLogHandler;

/**
 * Fetches Eve Online Market Groups
 *
 * Eve Online groups items by type. This class will allow
 * you to fetch that information.
 *
 * It is best you set up a back ground job (or set of) for This
 * class as it does return a series of data.
 *
 * This class works in two parts, One is to fetch the groups.
 * The second part is to fetch the data for each item in the group.
 * Because some groups have multiple pages we also fetch all the pages
 * for the groups set of items.
 */
class MarketGroups {

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
     * Contains a particular groups set of items.
     *
     * Mapped as 'groupName' => responseJson
     */
    private $groupInformationContainer = [];

    /**
     * An array of GuzzleHttp\Psr7\Request.
     */
    private $createdRequests           = [];

    /**
     * An Array of GuzzleHttp\Psr7\Response.
     */
    private $acceptedResponses         = [];

    /**
     * Potential array of reasons why the request failed.
     */
    private $rejectedResponses         = [];

    public function __construct(Client $client, EveLogHandler $eveLogHandler) {
        $this->client        = $client;
        $this->eveLogHandler = $eveLogHandler;
    }

    /**
     * Fetches All the groups.
     *
     * @return decoded JSON of https://public-crest.eveonline.com/market/groups/
     */
    public function fetchGroupPages() {
        $response = $this->client->request('GET', 'https://public-crest.eveonline.com/market/groups/');

        $streamHandler = $this->eveLogHandler->setUpStreamHandler('eve_online_market_groups.log');
        $this->eveLogHandler->responseLog($response, $streamHandler);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Creates a set of requests.
     *
     * @param array $groups - Use the `fetchGroupPages()` method.
     */
    public function createRequestsForGroups(array $groups) {
        foreach($groups as $group) {
            array_push($this->createdRequests, new Request('GET', $group->types->href));
        }
    }

    /**
     * Uses the Guzzel Pool to process Requests.
     *
     * Processes with a concurrency of 18, since Eve limits to 20.
     *
     * Uses a promise and will wait until finished.
     */
    public function fetchGroupsInfromation() {

        $pool = new Pool($this->client, $this->createdRequests, [
            'concurrency' => 18,
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

    /**
     * Returns an array of accepted responses.
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function getAcceptedResponses() {
        return $this->acceptedResponses;
    }

    /**
     * Returns Either False or a container.
     *
     * The container will contain 'groupName' => 'responseJSON'.
     *
     * @param array $acceptedResponses - use `getAcceptedResponses()`
     * @param array $groups these should be the groups you passed in for this request.
     */
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
