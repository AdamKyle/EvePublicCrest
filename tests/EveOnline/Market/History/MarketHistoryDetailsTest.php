<?php
use EveOnline\Market\History\MarketHistoryDetails;

class MarketHistoryDetailsTest extends \PHPUnit_Framework_TestCase {

    public function testShouldReturnHistoryDetails() {
        $marketHistoryDetails = new MarketHistoryDetails(['sample'], [[1, 2]]);

        $marketHistoryDetails->createHistoryDetails();
        $this->assertNotEmpty($marketHistoryDetails->getHistoryDetails());
    }
}
