# Eve Online Orders

Getting orders for a region be it buy or sell in eve online can be rather easy.

We can either get the buy and sell orders of an item regardless of NPC or Player
for an item in either one region or in all regions.

## Install and Dependencies

This is class is used in conjunction with the rest of the library. It is designed to be used with Laravel 5.2 and Higher.

### Laravel 5.2 Setup

in the `config/app.php`:

```php
<?php

return [

    ...

    'providers' => [

        ...

        EveOnline\Market\Orders\OrderProvider::class,

    ],

    'aliases' => [

        ...

        'EveRegionOrders' => EveOnline\Market\Orders\OrderFacade::class,
    ],
];
```

This class creates the following log: `eve_online_region_details.log`, `eve_online_buy_orders.log`, `eve_online_region_details` in `storage/logs` of your laravel application.


## Usage

So how do we get the orders once we have things set up?

To get the buy orders for a specific region you just need to call:

```php
$regionDetails = EveRegionOrders::getRegionDetailsJson($regionUrlHere);
$orders        = EveRegionOrders::getBuyDetails($itemTypeHref, $regionDetails);
```

This sets up the region url details to then be passed into the `getBuyDetails` so we can get all the buy
details for that specific region.

Whats the region url? For example: `https://public-crest.eveonline.com/regions/11000001/`

You will also need the item type href, an example of one is: `https://public-crest.eveonline.com/types/32772/``

To get sell orders its the same process:

```php
$regionDetails = EveRegionOrders::getRegionDetailsJson($regionUrlHere);
$orders        = EveRegionOrders::getSellDetails($itemTypeHref, $regionDetails);
```

But what if you want to search all or a set of regions for either buy or sell orders of that item? We will need a bit more set up but the process is fairly straight forward:

```php
$orderHandler           = new OrderHandler($this->client);
$orders                 = EveRegionOrders::searchAllRegionsForOrders($regionHrefs, $orderHandler);
$responses              = $orderHandler->getAcceptedResponsesJson();
$createdRequestsForPool = EveRegionOrders::createRequestsForMarketDetailsPool($responses, $this->itemTypeHref, $buying);
$orderDetails           = EveRegionOrders::getOrderResponsesFromRegionSearch($orderHandler, $createdRequestsForPool);
```

Lets break this down.

First you need to create an instance of the `EveOnline\Market\Orders\OrderHandler` which takes an instance of `GuzzleHttp\Client`. Super basic.

Next we need an array of region hrefs, an example is: `['https://public-crest.eveonline.com/regions/11000001/']` These are going to be the regions we search through.

We need to get the response json of that pool request back. We do 18 concurrent connections with: `searchAllRegionsForOrders` and wait until they are all done. We get the responses of that pool by doing:
`$orderHandler->getAcceptedResponsesJson()` This gives us each of the regions details that you want to search through.

Next we use that information to create a series of `GuzzleHttp\Psr7\Request`'s which are finally passed to:
`getOrderResponsesFromRegionSearch` which gets either the selling or the buying orders for each region based off the item type href passed to the `createRequestsForMarketDetailsPool`, which again can be: `https://public-crest.eveonline.com/types/32772/`.

`$orderDetails` will be an array of objects. Each object will contain information about the buy and sell orders for that item.
