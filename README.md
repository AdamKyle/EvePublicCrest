# EVE Online Public Crest Market API - PHP

[![Build Status](https://travis-ci.org/AdamKyle/EvePublicCrest.svg?branch=master)](https://travis-ci.org/AdamKyle/EvePublicCrest)
[![Packagist](https://img.shields.io/packagist/v/evemarket/eve-market-details.svg?style=flat)](https://packagist.org/packages/evemarket/eve-market-details)
[![Maintenance](https://img.shields.io/maintenance/yes/2016.svg)]()
[![Made With Love](https://img.shields.io/badge/Made%20With-Love-green.svg)]()

Eve Online Public Crest Market API is a set of Laravel classes that can be used in a Laravel project and is in fact tied to Laravel as a dependency.

We provide the providers, facade and the core classes each documented with its own README.

Each class and call creates a log (or set of) that is stored in the Laravel `storage/logs`.

## API Key?

You do not ned a token or an API key to access the EVE Online Public Crest API.

## Return Values

All Api Calls, documented below, will return either decoded JSON or a container. The container will be an array of arrays.

Decoded JSON allows you to do things like `$decodedJSON->property->nestedProperty` You will need to view the appropriate end point of each API call facade to seen what the data would look like coming back.

Containers are also created to store either a set of pages, so it would be an array of arrays each array in the parent array would contain a decoded JSON object or it might be an array of arrays where the array in the parent array contains other data such as id's as well as the decoded JSON object.

We suggest you store this data inside a database of some kind.

## The Logs Give me a bunch of Curl 52 Errors

These are unavoidable. They Are hard to track down and even harder to deal with. You cannot catch them in a try catch.
This seems to be an issue with the EVE Online Public Crest API or an issue with Guzzle or your environment.

How ever Eve Public Crest allows for 20 concurrent connections when we do pool based requests. We do only 18 as to not hit the rate limit. If you see any 503 errors then we have hit the rate limit and thats an issue with us.

### What do I do about curl 52 errors?

I am unsure. If you have a solutions I would gladly take a PR.

## Classes

Each of the classes are documented below. Each class has its own set up and use documentation.

- [Items/Details](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Items/README.md)
- [Market/Groups/MarketGroups](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Groups/README.md)
- [Market/History/MarketHistory](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/History/README.md)
- [Market/Orders/Order](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Orders/README.md)
- [Market/Prices/Prices](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Prices/README.md)
- [Market/Types/Types](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Types/README.md)
- [Regions/Regions](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Regions/README.md)

## Logs and responses

Each of the classes have a series of data that they log and return. All pool requests and guzzle requests are logged to there appropriate set of files.

## Regarding URL Information.

Some of the classes and there associated methods that fetch data either build or require a url to be passed in. Nine times out of ten a previous class method or call has fetched you a response that contains this information for you.

As a result it is assumed you are saving this information to a database some where so that you can then access the information that some of the other methods require easier with out running into errors.

All of the information you need for each request can be found (and is documented) in a previous call.

## Contributing

All PR's made will run against Travis CI and will not be accepted until they are passing. All PR's are welcome.

All Issues are welcome.
