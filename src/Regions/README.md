# Eve Online Regions

Eve Online Regions is a class allows you to get all regions from Eve Online via the public crest.

> ### ATTN!
>
> It will be your job to filter out all worm hole and CCP specific regions. These are usually regions with a dash in
> there name.

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

        EveOnline\Regions\RegionsProvider::class,

    ],

    'aliases' => [

        ...

        'EveRegions' => EveOnline\Regions\RegionsFacade::class,
    ],
];
```

This class generates the following log: `eve_online_regions.log` Which is then stored in the `storage/logs`.

## Quick Use

To fetch regions:

```php
EveRegions::regions();
```

The above will return a decoded JSON object that you can then use and save to your database.[https://public-crest.eveonline.com/regions/](https://public-crest.eveonline.com/regions/)
