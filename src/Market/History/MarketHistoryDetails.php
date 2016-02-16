<?php

namespace EveOnline\Market\History;

class MarketHistoryDetails {

    private $acceptedResponses;

    private $regionItemPairs;

    private $historyDetails = [];

    public function __construct(array $acceptedResponses, array $regionItemPairs) {
        $this->acceptedResponses = $acceptedResponses;
        $this->regionItemPairs   = $regionItemPairs;
    }

    public function createHistoryDetails() {

        foreach ($this->regionItemPairs as $index => $regionItemPair) {
            if (isset($this->acceptedResponses[$index])) {
                array_push($this->historyDetails, [
                    'regionId'     => $regionItemPair[1],
                    'itemId'       => $regionItemPair[0],
                    'responseJson' => $this->acceptedResponses[$index]
                ]);
            }
        }
    }

    public function getHistoryDetails() {
        return $this->historyDetails;
    }
}
