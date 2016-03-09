# Eve Online Market History

When you want to get historical data from Eve online for an item in a particular region there is no better place to turn.

> ### ATTN!!!
>
> This particular class is best used in conjunction with the [laravel job system](https://laravel.com/docs/5.1/queues).

## Install and Dependencies

in the `config/app.php`:

```php
<?php

return [

    ...

    'providers' => [

        ...

        EveOnline\Market\History\MarketHistoryProvider::class,

    ],

    'aliases' => [

        ...

        'EveItemHistory'  => EveOnline\Market\History\MarketHistoryFacade::class
    ],
];
```

This class generates the following logs, all of which are stored in `storage/logs`:

- `eve_online_region_item_history_rejected_responses.log`

> ### ATTN!
>
> We do not log successful responses because this can generate a file in the gigabytes.

## Usage

We recommend a job to be created in order to handle the large amount of requests that can happen. We also suggest that
this runs as a back job. For example Fetching ~12000 items across 62 regions can take up to 24 hours and generates roughly 24 million rows of data.

So lets look at how a simple laravel job would look:

```php
<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Etis\Domain\Services\EveOnlineRegionItemHistoryService;
use EveOnline\Logging\EveLogHandler;

class FetchEveOnlineItemHistory extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $items;

    private $regions;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $regions, array $items)
    {
        $this->items   = $items;
        $this->regions = $regions;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $eveLogHandler               = new EveLogHandler();
        $eveOnlineItemHistoryService = new EveOnlineRegionItemHistoryService($eveLogHandler);

        $eveOnlineItemHistoryService->fetchItemsHistoryForRegions($this->items, $this->regions);
    }
}
```

This job is super simple. `$this->items` and `$this->regions` and nothing more then an array of item id's and region id's. We suggest chunking up the items, if there are more then 100, to 100 at a time. that generates a potential of 620
records.

> ### ATTN!!!
>
> We know that some of the items might not have a region history at all, so if the data for that item in that region has
> has a page count of 0, we will not add it to the historical data output.

So in the service what do we call to get the data? Assuming we are only doing 100 at a time:

```php
EveItemHistory::createRequests($items, $regions);

$fetchedItemHistory = EveItemHistory::getItemHistoryForRegion(-20, function(array $regionItemPairs, $responseJson){
  // Do work here ...
});
```

We can see that we pass -20 to the `getItemHistoryForRegion`. This means that when we get the response of historical information, we go through the items property which is an array and return the last 20 (`array_slice`).

The Eve Api does not allow us to query it on its own, it only allows us to fetch the data and we have to query it our selves.

As you can see the second argument is a call back function. This allows us to process each item as it's finished being fetched from the eve online public crest api.

The two arguments that are injected are the region and item pair that matches the response json that comes back from the request that guzzle makes.

So, for example:

```php
dd($regionItemPairs, $responseJson); // with us only getting the last 20 items from the response

// Out puts something like:

array:2 [
  0 => 10000005         //=> Region ID
  1 => 37               //=> Item ID
]
{#13361
  +"totalCount_str": "274"
  +"items": array:20 [
    0 => {#12986
      +"volume_str": "79574"
      +"orderCount": 3
      +"lowPrice": 73.08
      +"highPrice": 73.08
      +"avgPrice": 73.08
      +"volume": 79574
      +"orderCount_str": "3"
      +"date": "2016-01-22T00:00:00"
    }
    1 => {#12985
      +"volume_str": "299"
      +"orderCount": 1
      +"lowPrice": 103.32
      +"highPrice": 103.32
      +"avgPrice": 103.32
      +"volume": 299
      +"orderCount_str": "1"
      +"date": "2016-01-24T00:00:00"
    }
    2 => {#12984
      +"volume_str": "302790"
      +"orderCount": 1
      +"lowPrice": 64.01
      +"highPrice": 64.01
      +"avgPrice": 64.01
      +"volume": 302790
      +"orderCount_str": "1"
      +"date": "2016-01-25T00:00:00"
    }
    3 => {#12983
      +"volume_str": "819853"
      +"orderCount": 4
      +"lowPrice": 80.0
      +"highPrice": 80.0
      +"avgPrice": 80.0
      +"volume": 819853
      +"orderCount_str": "4"
      +"date": "2016-01-30T00:00:00"
    }
    4 => {#12982
      +"volume_str": "57595"
      +"orderCount": 1
      +"lowPrice": 75.42
      +"highPrice": 75.42
      +"avgPrice": 75.42
      +"volume": 57595
      +"orderCount_str": "1"
      +"date": "2016-01-31T00:00:00"
    }
    5 => {#12981
      +"volume_str": "40000"
      +"orderCount": 4
      +"lowPrice": 72.13
      +"highPrice": 72.13
      +"avgPrice": 72.13
      +"volume": 40000
      +"orderCount_str": "4"
      +"date": "2016-02-01T00:00:00"
    }
    6 => {#12980
      +"volume_str": "50085"
      +"orderCount": 1
      +"lowPrice": 60.0
      +"highPrice": 60.0
      +"avgPrice": 60.0
      +"volume": 50085
      +"orderCount_str": "1"
      +"date": "2016-02-02T00:00:00"
    }
    7 => {#12979
      +"volume_str": "76653"
      +"orderCount": 1
      +"lowPrice": 72.13
      +"highPrice": 72.13
      +"avgPrice": 72.13
      +"volume": 76653
      +"orderCount_str": "1"
      +"date": "2016-02-03T00:00:00"
    }
    8 => {#12978
      +"volume_str": "249126"
      +"orderCount": 2
      +"lowPrice": 90.0
      +"highPrice": 90.0
      +"avgPrice": 90.0
      +"volume": 249126
      +"orderCount_str": "2"
      +"date": "2016-02-07T00:00:00"
    }
    9 => {#12977
      +"volume_str": "62154"
      +"orderCount": 3
      +"lowPrice": 90.0
      +"highPrice": 90.0
      +"avgPrice": 90.0
      +"volume": 62154
      +"orderCount_str": "3"
      +"date": "2016-02-08T00:00:00"
    }
    10 => {#12976
      +"volume_str": "405"
      +"orderCount": 1
      +"lowPrice": 118.0
      +"highPrice": 118.0
      +"avgPrice": 118.0
      +"volume": 405
      +"orderCount_str": "1"
      +"date": "2016-02-10T00:00:00"
    }
    11 => {#12975
      +"volume_str": "165001"
      +"orderCount": 1
      +"lowPrice": 118.0
      +"highPrice": 118.0
      +"avgPrice": 118.0
      +"volume": 165001
      +"orderCount_str": "1"
      +"date": "2016-02-11T00:00:00"
    }
    12 => {#12974
      +"volume_str": "41673"
      +"orderCount": 3
      +"lowPrice": 69.55
      +"highPrice": 69.55
      +"avgPrice": 69.55
      +"volume": 41673
      +"orderCount_str": "3"
      +"date": "2016-02-13T00:00:00"
    }
    13 => {#12973
      +"volume_str": "53197"
      +"orderCount": 1
      +"lowPrice": 55.0
      +"highPrice": 55.0
      +"avgPrice": 55.0
      +"volume": 53197
      +"orderCount_str": "1"
      +"date": "2016-02-14T00:00:00"
    }
    14 => {#12972
      +"volume_str": "198473"
      +"orderCount": 4
      +"lowPrice": 95.95
      +"highPrice": 95.95
      +"avgPrice": 95.95
      +"volume": 198473
      +"orderCount_str": "4"
      +"date": "2016-02-15T00:00:00"
    }
    15 => {#12971
      +"volume_str": "896603"
      +"orderCount": 4
      +"lowPrice": 64.01
      +"highPrice": 64.01
      +"avgPrice": 64.01
      +"volume": 896603
      +"orderCount_str": "4"
      +"date": "2016-02-17T00:00:00"
    }
    16 => {#12970
      +"volume_str": "314212"
      +"orderCount": 1
      +"lowPrice": 64.01
      +"highPrice": 64.01
      +"avgPrice": 64.01
      +"volume": 314212
      +"orderCount_str": "1"
      +"date": "2016-02-19T00:00:00"
    }
    17 => {#12969
      +"volume_str": "13351"
      +"orderCount": 1
      +"lowPrice": 90.0
      +"highPrice": 90.0
      +"avgPrice": 90.0
      +"volume": 13351
      +"orderCount_str": "1"
      +"date": "2016-02-20T00:00:00"
    }
    18 => {#12968
      +"volume_str": "189976"
      +"orderCount": 2
      +"lowPrice": 99.99
      +"highPrice": 99.99
      +"avgPrice": 99.99
      +"volume": 189976
      +"orderCount_str": "2"
      +"date": "2016-02-22T00:00:00"
    }
    19 => {#12967
      +"volume_str": "93215"
      +"orderCount": 1
      +"lowPrice": 115.0
      +"highPrice": 115.0
      +"avgPrice": 115.0
      +"volume": 93215
      +"orderCount_str": "1"
      +"date": "2016-02-24T00:00:00"
    }
  ]
  +"pageCount": 1
  +"pageCount_str": "1"
  +"totalCount": 274
}

```

> ### Getting Region Id's and Item Id's
>
> This is where you will need to make us of two facades:
> [Regions Facade](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Regions/README.md)
> and [Eve Online Market Types](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Types/README.md)
> Both of these will get you the details you need.
>
> You will want to save the information to a database, the particular information you care about is the `type->id`
> from the JSON resulting in visiting: [https://public-crest.eveonline.com/market/types/](https://public-crest.eveonline.com/market/types/) and the `id` from
> each region when visiting the [https://public-crest.eveonline.com/regions/](https://public-crest.eveonline.com/regions/). This information is also
> best saved to the database.
>
> ### URL used
>
> The url used to fetch data is: `'https://public-crest.eveonline.com/market/'.$region.'/types/'.$item.'/history/'`
>
> Where `$region` is the region id and `$item` is the item id.
>
> ### Note on the region and item id
>
> These are not id's that correlate to your database, these are the id's from the public crest api response for fetching  > regions and items, each region has a unique id and each item has a unique id, these are what I call "Eve Id's".
>
> You can see them by checking out either [Region JSON](https://public-crest.eveonline.com/regions/) or
> [Item Type JSON](https://public-crest.eveonline.com/types/)
