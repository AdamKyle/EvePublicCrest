# EVE Online Public Crest Market API - PHP

[![Build Status](https://travis-ci.org/AdamKyle/EvePublicCrest.svg?branch=master)](https://travis-ci.org/AdamKyle/EvePublicCrest)
[![Packagist](https://img.shields.io/packagist/v/evemarket/eve-market-details.svg?style=flat)](https://packagist.org/packages/evemarket/eve-market-details)
[![Maintenance](https://img.shields.io/maintenance/yes/2016.svg)]()
[![Made With Love](https://img.shields.io/badge/Made%20With-Love-green.svg)]()

**Note**: Never trust the packagist icon badge. Version: 0.15.0

`composer require evemarket/eve-market-details`

Eve Online Public Crest is a library that allows you to fetch Market Details and Historical Details. We allow access to
things like Regions, Orders, Market Prices, Historical Data, Market Groups, Item Types and much more.

The Api of this library is straight forward, simple and easy to understand. It is important to understand that Eve's Public Crest API only allows 20 concurrent connections, we only create 18.

You can use this library with [Eve Public Crest Laravel Bridge Extension](https://github.com/AdamKyle/Eve-Public-Crest-Laravel-Bridge-Extension). All you have to do is install the extension via composer, register the provider and associated facades and be on your way.

You can use this library and a stand alone. The key importance here is that if you use this with Laravel then instead
creating a new instance of the class you'll do `ClassName::methodName()`

## API Key?

You do not need a token or an API key to access the EVE Online Public Crest API.

## Return Values

All API calls documented below will return either a `GuzzleHttp\Psr7\Response` object in the form of a call back or a container of data.

Some methods will require both a successful and rejected callback function to be passed in with appropriate parameters, as documented below.

We also suggest you save all relevant data, such as href specific data to a database. This allows you to pull from the database and fetch href's to then be passed in to gather other data. You should not need to ever construct the url your self as most of the relevant data is in a response. This libraries components are designed to work together to fetch any specific piece of information you need.

## What this library is not

This library is not designed to fetch kill reports or any other aspect of Eve that is not directly related to the market in any fashion.

## The Logs Give me a bunch of Curl 52 Errors

These are unavoidable. They Are hard to track down and even harder to deal with. You cannot catch them in a try catch.
This seems to be an issue with the EVE Online Public Crest API or an issue with Guzzle or your environment.

How ever Eve Public Crest allows for 20 concurrent connections when we do pool based requests. We do only 18 as to not hit the rate limit. If you see any 503 errors then we have hit the rate limit and thats an issue with us.

### What do I do about curl 52 errors?

I am unsure. If you have a solutions I would gladly take a PR.

## Item Specific information

Each item in Eve can be fetched from the [Market Types](https://github.com/AdamKyle/EvePublicCrest#market-types) class below. How ever should you need details on a specific item, you can use the items href, example: `https://crest-tq.eveonline.com/types/18/` You should When ever have to construct this url your self.

When you use the [Market Types](https://github.com/AdamKyle/EvePublicCrest#market-types) class below, you'll get a response back that contains objects which contain a `type->href` property. This property can be used to get the specific details of an item.

```php

// Guzzle Client:
$client      = new Client();

$itemDetails = new EveOnline\Items\Details($client);

$itemDetails->details($href, function($response){
  ...
});
```

The above should be super simple to understand. We inject a new client into the class instance, we then fetch the details for an item based off it's href and in a call back we can do something with the response that is returned. The response will be a `GuzzleHttp\Psr7\Response` object.

## The Market Classes

The market classes documented below contain classes like `MarketGroups`, `MarketHistory`, `Order`, `Prices` and `Types`.

These classes all relate to the market in some way and the data returned from there could either be a response object or a container of data for you to save to the database or do what you wish with.

Keep in mind, you should never have to construct a url your self. All of the Market related classes will contain some kind of href in there response or container object that allows you to pass it into another API call.

### Market Groups

Fetches all the groups available in Eve. This one can take a while so we suggest a job system. How ever the basics are simple:

```php
// Guzzle Client
$client       = new Client();
$marketGroups = new EveOnline\Market\Groups\MarketGroups($client);

// We use: https://crest-tq.eveonline.com/market/groups/ to get the groups.
$groups = marketGroups->fetchGroupPages(function($response){
  // Get the body and the bodies contents. Then decode the json.

  // Do other work.

  // Important:
  return $decodedJSONResponse;
});

// Because there will be a lot of groups you might want to chunk them up:
// Notice how we use items, an array of groups.
$groupChunks = array_chunk($groups->items, 100);

// create a series of jobs based off each chunk.
// For example, in laravel you might do:
foreach ($groupChunks as $chunk) {
    dispatch(new JobNameHere($chunk));
}

// We are only going to take one chunk of the $groupChunks for this example:
$groupsRequest     = marketGroups->createRequestsForGroups($groupChunks[0]);

$groupsInformation = marketGroups->fetchGroupsInfromation(function($reason, $index){
  // $reason is the reason why it failed. its a guzzle object or error object.
  // $index refers to the $groupsRequest[$index], as to which request failed.
});

$acceptedResponses = marketGroups->getAcceptedResponses();

// The above will show something like:
// [index => [[decodedJSONResponse], [decodedJSONResponse]]]

// Finally we need the container of data:
$groupsContainer = EveMarketGroups::getGroupInformationContainer($acceptedResponses, $groupChunks[0]);

// This will be an array of array where the key is the group name.
// ['groupName' => [[decodedJSONResponse], [decodedJSONResponse]]]
```

You will need to do your own filtering at this stage to only save the groups you want. There is no way to query the Public Crest API it's self. Our API returns you all the groups and there associated pages.

### Market History

If you ever wanted a set of historical data from a specific region with a specific or even set of, then this is class you will want. We **highly** suggest you use [Regions](https://github.com/AdamKyle/EvePublicCrest#regions) class and [Market Types](https://github.com/AdamKyle/EvePublicCrest#market-types) class to get relevant information for this classes functions.

First of all you want to get the region id's and item's from the database. These are not the database id's these are Eve's id's For example: [Eve Regions](https://crest-tq.eveonline.com/regions/) have an id field of: `id: 11000001` for example.

[Eve Types](https://crest-tq.eveonline.com/market/types/) contain an array of object each with an id field: `id: 18` for example. These are the id's you will want. These can be fetched by using the suggested classes above and saving the relevant data to the database.

```php
// Assume you have a couple regions and a couple item id's. These ned to be arrays.

// Guzzle Client.
$client         = new Client();

$historicalData = EveOnline\Market\History\MarketHistory($client);

// Remember the params must be arrays.
$historicalData->createRequests($regionIds, $itemIds);

historicalData->getItemHistoryForRegion(-20, function(array $regionItemPair, $responseJson){

    // $regionItemPair - the array [$regionId, $itemId]
    // $responseJson - Example response: https://crest-tq.eveonline.com/market/10000002/types/34/history/

}, function($reason, $index) {
  // $reason, guzzle object or error object stating why it failed.
  // $index, the index of responses created to tell you which response failed.
});
```
## Market Orders

Market orders allow you to get the selling and buying order from a specific region for a specific item.

We **highly** suggest you use [Regions](https://github.com/AdamKyle/EvePublicCrest#regions) class and [Market Types](https://github.com/AdamKyle/EvePublicCrest#market-types) class to get relevant information for this classes functions.

You will need the item type href which you can get from the response of [Market Types](https://github.com/AdamKyle/EvePublicCrest#market-types) and the region href, which you can also get from the [Regions](https://github.com/AdamKyle/EvePublicCrest#regions) response.

> ### ATTN!
>
> You should store the orders as you fetch them. While there might be new orders, you may want historical data.
>
> You should also only fetch on demand and not try and fetch every single order across all items and all regions.
> This would be computationally expensive.

```php
// Guzzle Client
$client = new Client();
$order  = new EveOnline\Market\Orders\Order($client);
```

To get a single buy order:

```php
$regionDetails = $order->getRegionDetailsJson($regionHref);
$orders        = $order->getBuyDetails($itemHref, $regionDetails);

if ($orders->totalCount !== 0) {
  // Do something ... We have buy orders for this region and item.
} else {
  // No buy orders. Tell the user.
}
```

To get a single sell order. Its the same concept as above:

```php
$regionDetails = $order->getRegionDetailsJson($regionHref);
$orders        = $order->getSellDetails($itemHref, $regionDetails);

if ($orders->totalCount !== 0) {
  // Do something ... We have sell orders for this region and item.
} else {
  // No sell orders. Tell the user.
}
```

But what if you want to search all the regions for any type of order?

```php
$regionHrefs = [];

// Push all the href's from the regions fetched and stored into the database onto a container.
foreach($regionsFromTheDatabase as $region) {
   array_push($regionHrefs, $region->href);
}

// We need an instance of Order handler:
$orderHandler           = new EveOnline\Market\Orders($this->client);

$orders                 = $order->searchAllRegionsForOrders($regionHrefs, $orderHandler);
$responses              = $orderHandler->getAcceptedResponsesJson();

// remember to pass in the $itemHref.
// isBuying represents two aspects: 1 are we searching all regions for buy orders? (true/false).
// if false then we search all regions for sell orders.
$createdRequestsForPool = $order->createRequestsForMarketDetailsPool($responses, $itemHref, $isBuying);

// Lets get all the order details back. This will be an array of details.
$orderDetails           = $order->getOrderResponsesFromRegionSearch($orderHandler, $createdRequestsForPool);

if (count($orderDetails) == 0) {
   return false;
}

foreach ($orderDetails as $order) {
   if ($order->totalCount !== 0) {
       // Store the orders.
   }
}

// If there are no orders across all regions then tell the user.
```

### Market Prices

This is a rather straight forward call. We make a call to [https://crest-tq.eveonline.com/market/prices/](https://crest-tq.eveonline.com/market/prices/) to get all the market prices in eve. This is refreshed every 24 hours, so make sure you have a cron job set up to run.

This is also the end point that CCP uses in game to show you a list of market prices when you view market details.

```php
// Guzzle Client
$client = new Client();
$prices = new EveOnline\Market\Prices\Prices($client);

$prices->prices(function($response) {
  // $response is a GuzzleHttp\Psr7\Response object.
});
```

### Market Types

Market Types gets you information about all the items known in eve. There are roughly over 10'000 items that are usable in game with a total of ~12k items that are both usable, not usable and test items from CCP. Make sure to do appropriate filtering.

This class should be used when ever you need to get information about an item such as Id, href and so on. Make sure to store the relevant bits of information in the database.

```php
// Guzzle Client
$client = new Client();
$types  = new EveOnline\Market\Types\Types($client);

$typesContainer = $types->fetchTypes();

// See: https://crest-tq.eveonline.com/market/types/ as an example.
```

We will fetch every single page from [https://crest-tq.eveonline.com/market/types/](https://crest-tq.eveonline.com/market/types/) and return an array of arrays, where each array inside the main holds the JSON response of that page.

## Regions

This call is rather simple and, much like [Market Types](https://github.com/AdamKyle/EvePublicCrest#market-types) we heavily suggest that you store this information in the database to be called upon for other classes when fetching market data such as history or region buy/sell orders.

```php
// Guzzle Client
$client  = new Client();
$regions = new EveOnline\Market\Regions\Regions($client);

$regions->regions(function($response){
  // $response is a GuzzleHttp\Psr7\Response object.
});

// See: https://crest-tq.eveonline.com/regions/ as an example.
```

We will fetch every single region known. You will want to filter out every region with a hyphen in the name. These are either wormholes or CCP specific regions. This should leave you with an array of roughly 62 regions that players can interact with.
