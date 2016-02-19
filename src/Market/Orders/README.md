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

> ### ATTN!!
>
> Region urls can be fetch via using the [regions facade](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Regions/README.md). Each region will contain
> a series of href's that can be used to get details: [https://public-crest.eveonline.com/regions/](https://public-crest.eveonline.com/regions/).
> For example the first region in the array of regions has an href. If you are storing these its a simple database call.
>
> ### What About `$itemTypeHref`?
>
> You can get this from using the [Eve Online Market Types](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Types/README.md) and storing the data returned
> as each item in the type's array contains an href.

This sets up the region url details to then be passed into the `getBuyDetails` so we can get all the buy
details for that specific region.

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
$createdRequestsForPool = EveRegionOrders::createRequestsForMarketDetailsPool($responses, $itemTypeHref, $buying);
$orderDetails           = EveRegionOrders::getOrderResponsesFromRegionSearch($orderHandler, $createdRequestsForPool);
```

Lets break this down.

First you need to create an instance of the `EveOnline\Market\Orders\OrderHandler` which takes an instance of `GuzzleHttp\Client`. Super basic.

Next we need an array of region hrefs, an example is: `['https://public-crest.eveonline.com/regions/11000001/']` These are going to be the regions we search through.

Again make sure you use the [regions facade](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Regions/README.md) to get the region data which contains the href's so you are not building them your selves.

We need to get the response json of that pool request back. We do 18 concurrent connections with: `searchAllRegionsForOrders` and wait until they are all done. We get the responses of that pool by doing:
`$orderHandler->getAcceptedResponsesJson()` This gives us each of the regions details that you want to search through.

Next we use that information to create a series of `GuzzleHttp\Psr7\Request`'s which are finally passed to:
`getOrderResponsesFromRegionSearch` which gets either the selling or the buying orders for each region based off the item type href passed to the `createRequestsForMarketDetailsPool`, which again can be: `https://public-crest.eveonline.com/types/32772/`.

Again make sure you use the [Eve Online Market Types](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Types/README.md) to get the types data which contains the href's so you are not building them your selves.

`$orderDetails` will be an array of objects. Each object will contain information about the buy and sell orders for that item.

From here you would want to store the individual object, which can be seen in [this example here](https://public-crest.eveonline.com/market/10000002/orders/sell/?type=https://public-crest.eveonline.com/types/34/) should then be stored into a database for historical records or discarded.

You will want to fetch this information on demand. because it can take a few seconds we suggest showing the user a loading bar or a spinning wheel.

## Curl 52 Errors

Occasionally and very rarely in production you might get a curl 52 error. You can read more about that [here](https://github.com/AdamKyle/EvePublicCrest#what-do-i-do-about-curl-52-errors)
