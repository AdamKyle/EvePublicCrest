# Eve Online Market Groups

Fetches All the Eve Market Groups and there associated items.

Because each group may have multiple pages, we will return all pages for that group.

We recommend that this class be used with a job or set of. This is demonstrated below.

The following Eve Public Crest API's used are: [Groups](https://public-crest.eveonline.com/market/groups/) as well as
[https://public-crest.eveonline.com/market/types/?group=https://public-crest.eveonline.com/market/groups/2/](https://public-crest.eveonline.com/market/types/?group=https://public-crest.eveonline.com/market/groups/2/) Which is the groups type href as you will see by looking at a list of groups.

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

        EveOnline\Market\MarketGroupsProvider::class,

    ],

    'aliases' => [

        ...

        'EveMarketGroups' => EveOnline\Market\MarketGroupsFacade::class,
    ],
];
```

This class generates the following logs, all of which are stored in `storage/logs`:

- `eve_online_market_groups.log`
- `eve_online_group_items_responses.log`
- `eve_online_group_items_response_failures.log`
- `eve_online_item_response_addition_pages.log`


## How to use

There is no quick use because its important that when using Laravel you set up a job to use this class. The way I have done it in my personal projects is how I find it to be easiest to use. This may be different from how you do it how ever the concepts are all the same.

First of all lets get the group information:

```php
$groups = EveMarketGroups::fetchGroupPages();
// We use: https://public-crest.eveonline.com/market/groups/ to get the groups.

$groupChunks = array_chunk($groups->items, 100);

foreach ($groupChunks as $chunk) {
    dispatch(new FetchEveOnlineMarketGroupsInformation($chunk));
}
```

What we are doing here is saying: Fetch me the groupPages. In the future Eve might have multiple pages each containing a 1000 groups. Currently they have one.

Currently there are ~1370 groups. So we need to chunk these into 100 groups per array. This creates roughly 14 jobs. Next we use [Laravels Job System](https://laravel.com/docs/5.2/queues#writing-job-classes) to create a job, lets look at what my job class looks like:

```php
<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use \EveMarketGroups;
use App\Etis\Domain\Services\EveOnlineMarketGroupsService;

class FetchEveOnlineMarketGroupsInformation extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $groups = [];

    private $marketGroupService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;

        $this->marketGroupService = new EveOnlineMarketGroupsService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $groupsRequest     = EveMarketGroups::createRequestsForGroups($this->groups);

        // Each of the groups in: https://public-crest.eveonline.com/market/groups/ contains a type->href.
        // We use this to create the various Requests.

        $groupsInformation = EveMarketGroups::fetchGroupsInfromation();
        $acceptedResponses = EveMarketGroups::getAcceptedResponses();

        // Accepted responses are an array of:
        // [index => [[decodedJSONResponse], [decodedJSONResponse]]]

        $groupsContainer   = EveMarketGroups::getGroupInformationContainer($acceptedResponses, $this->groups);

        // This will be an array of array where the key is the group name.
        // ['groupName' => [[decodedJSONResponse], [decodedJSONResponse]]]

        $this->marketGroupService->storeGroupsInformation($groupsContainer);

        // The above is a call to store the container above in a database.
    }
}
```

In the `handle()` method you can see that I first create a set of requests from the 100 groups. Then I fetch the group
information which uses Guzzle's Pool class with a concurrency of 18 (20 is the limit for Eve). After which I pass the
accepted responses to the container along with the groups to create a container that looks something like:

```php
[
  'groupName' => [[decodedJSONResponse], [decodedJSONResponse]]
]
```

This is then passed to my service to then save the data to the database. But that step is entirely up to you. This whole process should be run as a background job at least once in the applications life time.

## Storage Suggestions.

If you have used [Eve Online Market Types](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Types/README.md) then we suggest that you store the item id that belongs to this group instead of the item details that comes back with each group fetch.

This allows your schema too look something like:

```
group_name
item_type_id
created_at
updated_at
```
