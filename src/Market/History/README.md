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

- `eve_online_region_item_history_responses.log`

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

$fetchedItemHistory = EveItemHistory::getItemHistoryForRegion(-20);
$historicalData     = EveItemHistory::getHistoricalData();
```

We can see that we pass -20 to the `getItemHistoryForRegion`. This means that when we get the response of historical information, we go through the items property which is an array and return the last 20 (`array_slice`).

The Eve Api does not allow us to query it on its own, it only allows us to fetch the data and we have to query it our selves.

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

First we use that array of item and region ids to create a set of PSR7 Guzzle Requests.

Second, we fetch the history and build the historical data.

Finally we fetch and return the historical data.

This historical data will contain x number of arrays inside an array, each array will look like:

```php
618 => array:3 [
  "regionId" => "10000068"
  "itemId" => "38"
  "responseJson" => {#152015 …5}
]
```

The `responseJson` is a decoded JSON object.
