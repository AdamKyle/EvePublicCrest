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

The easiest way in Laravel to use this class is: `EveMarketTypes::fetchTypes();`
