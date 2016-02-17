# Eve Online Prices

This is a rather simple and easy to use class that can return you all of the market prices for all items.
These items can be seen via visiting [this api](https://public-crest.eveonline.com/market/prices/).

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

        EveOnline\Market\PricesProvider::class,

    ],

    'aliases' => [

        ...

        'EvePrices' => EveOnline\Market\PricesFacade::class,
    ],
];
```

## Quick Use

To get started all you have to do to get a list of all prices is:

```php
EvePrices::prices();
```

There is nothing fancy to it. This will return [the resulting JSON](https://public-crest.eveonline.com/market/prices/)
