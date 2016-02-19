# Eve Online Types

Fetches all (at the time of this writing) 13 pages of market types from the market types API end point.

Should EVE Public Crest add more pages we will fetch those as well.

See the API end point [here](https://public-crest.eveonline.com/market/types/).

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

        EveOnline\Market\Types\TypesProvider::class,

    ],

    'aliases' => [

        ...

        'EveMarketTypes' => EveOnline\Market\Types\TypesFacade::class,
    ],
];
```

This class generates the following log: `eve_online_market_types.log` Which is then stored in the `storage/logs`.

> ###ATTN!!
>
> When using this class, the log file stores not only the response for the first page,
> but each subsequent page as well.

## Quick Use

The easiest way in Laravel to use this class is:

```php
EveMarketTypes::fetchTypes();
```

The following will return all current pages of all item types as an iterator that was turned into an array of responses.

For example if you visit: [https://public-crest.eveonline.com/market/types/](https://public-crest.eveonline.com/market/types/) you will see that there are x pages. Each page is stored in an array as a decoded json object.

We suggest you store the `items[x]->types->href` in the database in order to use with [Eve Online Item Details](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Items/README.md).
